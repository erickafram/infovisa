<?php

namespace Database\Seeders;

use App\Models\TipoDocumentoObrigatorio;
use Illuminate\Database\Seeder;

class DocumentosComunsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentosComuns = [
            [
                'nome' => 'CNPJ',
                'descricao' => 'Cartão CNPJ com data de impressão de até 30 dias',
                'documento_comum' => true,
                'escopo_competencia' => 'estadual',
                'tipo_setor' => 'todos',
                'prazo_validade_dias' => 30,
                'ordem' => 1,
                'ativo' => true,
            ],
            [
                'nome' => 'CONTRATO SOCIAL',
                'descricao' => 'Contrato Social - versão que identifica o responsável legal pela empresa',
                'documento_comum' => true,
                'escopo_competencia' => 'estadual',
                'tipo_setor' => 'privado',
                'observacao_privada' => 'Apenas para empresas privadas',
                'ordem' => 2,
                'ativo' => true,
            ],
            [
                'nome' => 'DARE',
                'descricao' => 'Documento de Arrecadação Estadual CÓDIGO 420 – SUBCÓDIGO: ver tabela no final deste documento',
                'documento_comum' => true,
                'escopo_competencia' => 'estadual',
                'tipo_setor' => 'privado',
                'observacao_publica' => 'Isento para estabelecimentos públicos',
                'ordem' => 3,
                'ativo' => true,
            ],
            [
                'nome' => 'COMP PAGAMENTO',
                'descricao' => 'Comprovante de Pagamento do DARE',
                'documento_comum' => true,
                'escopo_competencia' => 'estadual',
                'tipo_setor' => 'privado',
                'observacao_publica' => 'Isento para estabelecimentos públicos',
                'ordem' => 4,
                'ativo' => true,
            ],
            [
                'nome' => 'PARECER PROJETO',
                'descricao' => 'Parecer técnico de análise do projeto arquitetônico do estabelecimento ou comprovação que o projeto está em análise',
                'documento_comum' => true,
                'escopo_competencia' => 'estadual',
                'tipo_setor' => 'todos',
                'ordem' => 5,
                'ativo' => true,
            ],
        ];

        foreach ($documentosComuns as $documento) {
            TipoDocumentoObrigatorio::updateOrCreate(
                [
                    'nome' => $documento['nome'],
                    'documento_comum' => true,
                    'escopo_competencia' => 'estadual'
                ],
                $documento
            );
        }

        $this->command->info('✅ Documentos comuns obrigatórios para competência estadual criados com sucesso!');
        $this->command->info('📋 Total: ' . count($documentosComuns) . ' documentos');
    }
}