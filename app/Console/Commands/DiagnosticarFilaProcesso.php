<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Processo;
use App\Models\TipoProcesso;
use App\Models\Atividade;
use App\Models\ListaDocumento;
use App\Models\ProcessoDocumento;

class DiagnosticarFilaProcesso extends Command
{
    protected $signature = 'fila:diagnosticar {processo_id}';
    protected $description = 'Diagnostica por que um processo não aparece na fila pública';

    public function handle()
    {
        $processoId = $this->argument('processo_id');
        $processo = Processo::with('estabelecimento')->find($processoId);

        if (!$processo) {
            $this->error("Processo ID {$processoId} não encontrado.");
            return;
        }

        $this->info("=== DIAGNÓSTICO DO PROCESSO ===");
        $this->info("ID: {$processo->id}");
        $this->info("Número: {$processo->numero_processo}");
        $this->info("Tipo: {$processo->tipo}");
        $this->info("Status: {$processo->status}");
        $this->info("Estabelecimento: " . ($processo->estabelecimento->nome_fantasia ?? 'N/A'));
        $this->info("Município ID: " . ($processo->estabelecimento->municipio_id ?? 'NULL'));

        // 1. Verificar tipo de processo
        $tipo = TipoProcesso::where('codigo', $processo->tipo)->first();
        if (!$tipo) {
            $this->error("PROBLEMA: TipoProcesso com código '{$processo->tipo}' não encontrado.");
            return;
        }
        $this->info("\n--- Tipo de Processo ---");
        $this->info("Nome: {$tipo->nome}");
        $this->info("Ativo: " . ($tipo->ativo ? 'Sim' : 'NÃO'));
        $this->info("Fila pública: " . ($tipo->exibir_fila_publica ? 'Sim' : 'NÃO'));
        $this->info("Prazo fila: " . ($tipo->prazo_fila_publica ?? 'NULL'));

        if (!$tipo->ativo) $this->error("PROBLEMA: Tipo de processo INATIVO.");
        if (!$tipo->exibir_fila_publica) $this->error("PROBLEMA: Fila pública DESATIVADA para este tipo.");

        // 2. Verificar status
        $statusValidos = ['aberto', 'em_analise', 'pendente', 'parado'];
        if (!in_array($processo->status, $statusValidos)) {
            $this->error("PROBLEMA: Status '{$processo->status}' não está na lista de status válidos: " . implode(', ', $statusValidos));
        } else {
            $this->info("Status OK: '{$processo->status}' é válido para a fila.");
        }

        // 3. Verificar documentos obrigatórios
        $isProcessoEspecial = in_array($tipo->codigo, ['projeto_arquitetonico', 'analise_rotulagem']);
        $this->info("\n--- Documentos Obrigatórios ---");
        $this->info("Processo especial: " . ($isProcessoEspecial ? 'Sim' : 'Não'));

        $estabelecimento = $processo->estabelecimento;
        $atividadesExercidas = $estabelecimento->atividades_exercidas ?? [];
        $this->info("Atividades exercidas: " . count($atividadesExercidas));

        $atividadeIds = collect();
        if (!$isProcessoEspecial && !empty($atividadesExercidas)) {
            $codigosCnae = collect($atividadesExercidas)->map(function($atividade) {
                $codigo = is_array($atividade) ? ($atividade['codigo'] ?? null) : $atividade;
                return $codigo ? preg_replace('/[^0-9]/', '', $codigo) : null;
            })->filter()->values()->toArray();
            $atividadeIds = Atividade::where('ativo', true)
                ->where(function($query) use ($codigosCnae) {
                    foreach ($codigosCnae as $codigo) {
                        $query->orWhere('codigo_cnae', $codigo);
                    }
                })->pluck('id');
            $this->info("Atividade IDs encontrados: " . $atividadeIds->implode(', '));
        }

        // Busca listas
        $listasQuery = ListaDocumento::where('ativo', true)
            ->where('tipo_processo_id', $tipo->id)
            ->with(['tiposDocumentoObrigatorio' => function($q) {
                $q->orderBy('lista_documento_tipo.ordem');
            }]);

        if ($isProcessoEspecial) {
            $listasQuery->whereDoesntHave('atividades');
        } else {
            if ($atividadeIds->isEmpty()) {
                $this->error("PROBLEMA: Nenhuma atividade encontrada para o estabelecimento.");
                return;
            }
            $listasQuery->whereHas('atividades', function($q) use ($atividadeIds) {
                $q->whereIn('atividades.id', $atividadeIds);
            });
        }

        // Filtro de escopo
        $listasQuery->where(function($q) use ($estabelecimento) {
            $q->where('escopo', 'estadual');
            if ($estabelecimento->municipio_id) {
                $q->orWhere(function($q2) use ($estabelecimento) {
                    $q2->where('escopo', 'municipal')
                       ->where('municipio_id', $estabelecimento->municipio_id);
                });
            }
        });

        $listas = $listasQuery->get();
        $this->info("Listas encontradas: {$listas->count()}");

        foreach ($listas as $lista) {
            $this->info("  - Lista #{$lista->id}: {$lista->nome} (escopo: {$lista->escopo}, municipio_id: " . ($lista->municipio_id ?? 'NULL') . ")");
        }

        // Coleta docs obrigatórios
        $docsObrigatorios = collect();
        foreach ($listas as $lista) {
            foreach ($lista->tiposDocumentoObrigatorio as $tipoDoc) {
                if ($tipoDoc->pivot->obrigatorio && !$docsObrigatorios->contains('id', $tipoDoc->id)) {
                    $docsObrigatorios->push($tipoDoc);
                }
            }
        }

        $this->info("Docs obrigatórios: {$docsObrigatorios->count()}");

        if ($docsObrigatorios->isEmpty()) {
            $this->warn("Nenhum doc obrigatório encontrado. Processo seria considerado completo pela data de abertura.");
            return;
        }

        // Verifica cada doc
        $todosAprovados = true;
        foreach ($docsObrigatorios as $docObrigatorio) {
            $documento = ProcessoDocumento::where('processo_id', $processo->id)
                ->where('tipo_documento_obrigatorio_id', $docObrigatorio->id)
                ->where('status_aprovacao', 'aprovado')
                ->orderByRaw('COALESCE(aprovado_em, updated_at) DESC')
                ->first();

            if (!$documento) {
                $this->error("  ✗ {$docObrigatorio->nome} (ID: {$docObrigatorio->id}) — NÃO APROVADO");
                $todosAprovados = false;

                // Verifica se existe com outro status
                $qualquer = ProcessoDocumento::where('processo_id', $processo->id)
                    ->where('tipo_documento_obrigatorio_id', $docObrigatorio->id)
                    ->first();
                if ($qualquer) {
                    $this->warn("    Existe com status: {$qualquer->status_aprovacao}");
                } else {
                    $this->warn("    Nenhum documento enviado para este tipo.");
                }
            } else {
                $dataAprov = $documento->aprovado_em ?? $documento->updated_at;
                $this->info("  ✓ {$docObrigatorio->nome} — Aprovado em {$dataAprov}");
            }
        }

        $this->info("\n=== RESULTADO ===");
        if ($todosAprovados) {
            $this->info("✓ Todos os documentos obrigatórios estão aprovados. O processo DEVERIA aparecer na fila.");
            $this->warn("Se não aparece, verifique se há cache ou se o tipo de processo tem exibir_fila_publica = true.");
        } else {
            $this->error("✗ Documentação INCOMPLETA. O processo não aparece na fila por isso.");
        }
    }
}
