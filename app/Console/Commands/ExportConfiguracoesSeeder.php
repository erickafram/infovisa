<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportConfiguracoesSeeder extends Command
{
    protected $signature = 'export:configuracoes';
    protected $description = 'Exporta os dados de configurações para seeders';

    public function handle()
    {
        $this->info('Exportando configurações...');

        // Tabelas de configuração para exportar
        $tabelas = [
            'tipos_processo' => 'TipoProcesso',
            'tipos_documento' => 'TipoDocumento',
            'tipos_documento_obrigatorio' => 'TipoDocumentoObrigatorio',
            'tipo_acoes' => 'TipoAcao',
            'tipo_setores' => 'TipoSetor',
            'modelos_documento' => 'ModeloDocumento',
            'atividades' => 'Atividade',
            'listas_documento' => 'ListaDocumento',
            'configuracoes_sistema' => 'ConfiguracaoSistema',
        ];

        foreach ($tabelas as $tabela => $model) {
            $this->exportarTabela($tabela, $model);
        }

        // Exportar tabelas pivot
        $this->exportarTabelasPivot();

        $this->info('Exportação concluída! Verifique os arquivos em database/seeders/');
    }

    private function exportarTabela($tabela, $model)
    {
        try {
            $dados = DB::table($tabela)->get();
            
            if ($dados->isEmpty()) {
                $this->warn("Tabela {$tabela} está vazia, pulando...");
                return;
            }

            $this->info("Exportando {$tabela} ({$dados->count()} registros)...");

            $seederContent = $this->gerarSeederContent($tabela, $model, $dados);
            
            $filename = "database/seeders/{$model}Seeder.php";
            File::put(base_path($filename), $seederContent);
            
            $this->info("  -> Criado: {$filename}");
        } catch (\Exception $e) {
            $this->warn("Erro ao exportar {$tabela}: " . $e->getMessage());
        }
    }

    private function gerarSeederContent($tabela, $model, $dados)
    {
        $dataArray = $dados->map(function ($item) {
            return (array) $item;
        })->toArray();

        $dataExport = var_export($dataArray, true);
        
        // Formata melhor o array
        $dataExport = preg_replace('/^(\s*)array \(/m', '$1[', $dataExport);
        $dataExport = preg_replace('/\)$/m', ']', $dataExport);
        $dataExport = str_replace('array (', '[', $dataExport);
        $dataExport = str_replace(')', ']', $dataExport);

        return <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class {$model}Seeder extends Seeder
{
    /**
     * Seed the {$tabela} table.
     * Gerado automaticamente em: {$this->now()}
     */
    public function run(): void
    {
        \$dados = {$dataExport};

        foreach (\$dados as \$item) {
            // Remove campos de timestamp para inserção limpa
            unset(\$item['created_at'], \$item['updated_at']);
            
            DB::table('{$tabela}')->updateOrInsert(
                ['id' => \$item['id']],
                \$item
            );
        }
        
        \$this->command->info('{$model}: ' . count(\$dados) . ' registros inseridos/atualizados');
    }
}
PHP;
    }

    private function exportarTabelasPivot()
    {
        $pivots = [
            'lista_documento_atividade' => 'ListaDocumentoAtividade',
            'lista_documento_tipo' => 'ListaDocumentoTipo',
        ];

        foreach ($pivots as $tabela => $nome) {
            try {
                $dados = DB::table($tabela)->get();
                
                if ($dados->isEmpty()) {
                    continue;
                }

                $this->info("Exportando pivot {$tabela} ({$dados->count()} registros)...");

                $dataArray = $dados->map(fn($item) => (array) $item)->toArray();
                $dataExport = var_export($dataArray, true);
                $dataExport = preg_replace('/^(\s*)array \(/m', '$1[', $dataExport);
                $dataExport = preg_replace('/\)$/m', ']', $dataExport);
                $dataExport = str_replace('array (', '[', $dataExport);
                $dataExport = str_replace(')', ']', $dataExport);

                $content = <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class {$nome}Seeder extends Seeder
{
    public function run(): void
    {
        \$dados = {$dataExport};

        DB::table('{$tabela}')->truncate();
        
        foreach (\$dados as \$item) {
            DB::table('{$tabela}')->insert(\$item);
        }
        
        \$this->command->info('{$nome}: ' . count(\$dados) . ' registros inseridos');
    }
}
PHP;

                File::put(base_path("database/seeders/{$nome}Seeder.php"), $content);
                $this->info("  -> Criado: database/seeders/{$nome}Seeder.php");
            } catch (\Exception $e) {
                $this->warn("Erro ao exportar {$tabela}: " . $e->getMessage());
            }
        }
    }

    private function now()
    {
        return now()->format('Y-m-d H:i:s');
    }
}
