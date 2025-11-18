<?php

namespace App\Console\Commands;

use App\Models\DiarioBuscaAlerta;
use App\Models\DiarioBuscaSalva;
use App\Services\DiarioOficialService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerificarNovosDiarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diario:verificar-novos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica novos resultados para buscas salvas do Diário Oficial configuradas para execução diária';

    /**
     * Execute the console command.
     */
    public function handle(DiarioOficialService $diarioService)
    {
        $this->info('Iniciando verificação de buscas automáticas do Diário Oficial...');
        
        $buscas = DiarioBuscaSalva::where('executar_diariamente', true)->get();
        
        if ($buscas->isEmpty()) {
            $this->info('Nenhuma busca automática configurada.');
            return;
        }

        $this->info("Encontradas {$buscas->count()} buscas para processar.");
        
        // Data de hoje para a busca
        // A lógica é: buscar nas edições de hoje (ou dos últimos dias se não rodou recentemente)
        // Vamos simplificar: buscar sempre na data de hoje (o serviço já lida com cache se necessário)
        // Ou melhor: buscar de "ontem" até "hoje" para garantir que edições publicadas tarde da noite sejam pegas
        
        $hoje = Carbon::today();
        $ontem = Carbon::yesterday();
        
        foreach ($buscas as $busca) {
            try {
                $this->info("Processando busca ID {$busca->id}: {$busca->nome} (Usuario: {$busca->usuario_interno_id})");
                
                // Definir intervalo de busca
                // Se ultima_execucao for null, buscar dos ultimos 7 dias? Não, respeitar data inicial da busca original?
                // Melhor: Buscar apenas edições recentes (hoje/ontem) pois é um alerta de "novidade".
                
                $resultados = $diarioService->buscar(
                    $busca->texto,
                    $ontem->format('Y-m-d'),
                    $hoje->format('Y-m-d')
                );
                
                if (!empty($resultados)) {
                    $novosResultados = 0;
                    
                    foreach ($resultados as $resultado) {
                        // Verificar se já existe alerta para este resultado (mesmo título/data/edicao)
                        $exists = DiarioBuscaAlerta::where('diario_busca_salva_id', $busca->id)
                            ->where('data_publicacao', $resultado['data_publicacao'])
                            ->where('titulo', $resultado['titulo'])
                            ->exists();
                            
                        if (!$exists) {
                            DiarioBuscaAlerta::create([
                                'diario_busca_salva_id' => $busca->id,
                                'usuario_interno_id' => $busca->usuario_interno_id,
                                'titulo' => $resultado['titulo'],
                                'edicao' => $resultado['numero_edicao'] ?? null,
                                'data_publicacao' => $resultado['data_publicacao'],
                                'url_download' => $resultado['url_download'] ?? null,
                                'lido' => false
                            ]);
                            $novosResultados++;
                        }
                    }
                    
                    if ($novosResultados > 0) {
                        $this->info("  -> {$novosResultados} novos resultados encontrados e alertados.");
                    } else {
                        $this->info("  -> Nenhum resultado novo.");
                    }
                } else {
                    $this->info("  -> Nenhum resultado encontrado.");
                }
                
                // Atualizar última execução
                $busca->update(['ultima_execucao' => now()]);
                
            } catch (\Exception $e) {
                $this->error("Erro ao processar busca ID {$busca->id}: " . $e->getMessage());
                Log::error("Erro no comando diario:verificar-novos", [
                    'busca_id' => $busca->id,
                    'erro' => $e->getMessage()
                ]);
            }
            
            // Pequena pausa para não sobrecarregar a API externa
            sleep(1);
        }
        
        $this->info('Verificação concluída.');
    }
}
