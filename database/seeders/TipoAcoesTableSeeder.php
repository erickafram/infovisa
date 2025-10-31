<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoAcoesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposAcoes = [
            // I. CADASTRO ESTABELECIMENTOS
            [
                'descricao' => 'Cadastro de estabelecimentos sujeitos à vigilância sanitária',
                'codigo_procedimento' => '01.02.01.007-2',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Cadastro de instituições de longa permanência para idosos',
                'codigo_procedimento' => '01.02.01.027-7',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Cadastro de hospitais',
                'codigo_procedimento' => '01.02.01.025-0',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Cadastro de indústrias de medicamentos',
                'codigo_procedimento' => '01.02.01.030-7',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Cadastro de indústrias de insumos farmacêuticos',
                'codigo_procedimento' => '01.02.01.054-4',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Cadastro de indústrias de produtos para saúde',
                'codigo_procedimento' => '01.02.01.055-2',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Cadastro de estabelecimentos de serviços de alimentação',
                'codigo_procedimento' => '01.02.01.045-5',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            
            // SERVIÇOS
            [
                'descricao' => 'Cadastro de serviços de diagnóstico e tratamento do câncer de colo de útero e mama',
                'codigo_procedimento' => '01.02.01.033-1',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Cadastro de serviços hospitalares de atenção ao parto e à criança',
                'codigo_procedimento' => '01.02.01.036-6',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Cadastro de serviços de hemoterapia',
                'codigo_procedimento' => '01.02.01.039-0',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Cadastro de serviços de terapia renal substitutiva',
                'codigo_procedimento' => '01.02.01.042-0',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            
            // EXCLUSÃO DE CADASTRO
            [
                'descricao' => 'Exclusão de cadastro de estabelecimentos sujeitos à vigilância sanitária com atividades encerradas',
                'codigo_procedimento' => '01.02.01.016-1',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            
            // II. INSPEÇÃO ESTABELECIMENTOS
            [
                'descricao' => 'Inspeção dos estabelecimentos sujeitos à vigilância sanitária',
                'codigo_procedimento' => '01.02.01.017-0',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Inspeção sanitária de instituições de longa permanência para Idosos',
                'codigo_procedimento' => '01.02.01.028-5',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Inspeção sanitária de hospitais',
                'codigo_procedimento' => '01.02.01.014-5',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Inspeção sanitária de indústrias de medicamentos',
                'codigo_procedimento' => '01.02.01.031-5',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Inspeção sanitária de indústrias de insumos farmacêuticos',
                'codigo_procedimento' => '01.02.01.056-0',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Inspeção sanitária de indústrias de produtos para saúde',
                'codigo_procedimento' => '01.02.01.057-9',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Inspeção sanitária de estabelecimentos de serviços de alimentação',
                'codigo_procedimento' => '01.02.01.046-3',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            
            // SERVIÇOS
            [
                'descricao' => 'Inspeção sanitária de serviços de diagnóstico e tratamento do câncer de colo de útero e mama',
                'codigo_procedimento' => '01.02.01.034-0',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Inspeção sanitária de serviços hospitalares de atenção ao parto e à criança',
                'codigo_procedimento' => '01.02.01.037-4',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Inspeção sanitária de serviços de hemoterapia',
                'codigo_procedimento' => '01.02.01.040-4',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Inspeção sanitária de serviços de terapia renal substitutiva',
                'codigo_procedimento' => '01.02.01.043-9',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            
            // III. LICENCIAMENTO ESTABELECIMENTOS
            [
                'descricao' => 'Licenciamento dos estabelecimentos sujeitos à vigilância sanitária',
                'codigo_procedimento' => '01.02.01.018-8',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Licenciamento sanitário de instituições de longa permanência para idosos',
                'codigo_procedimento' => '01.02.01.029-3',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Licenciamento sanitário de hospitais',
                'codigo_procedimento' => '01.02.01.026-9',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Licenciamento sanitário de indústrias de medicamentos',
                'codigo_procedimento' => '01.02.01.032-3',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Licenciamento sanitário de estabelecimentos de serviços de alimentação',
                'codigo_procedimento' => '01.02.01.047-1',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            
            // SERVIÇOS
            [
                'descricao' => 'Licenciamento sanitário de serviços de diagnóstico e tratamento do câncer de colo de útero e mama',
                'codigo_procedimento' => '01.02.01.035-8',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Licenciamento sanitário de serviços hospitalares de atenção ao parto e a criança',
                'codigo_procedimento' => '01.02.01.038-2',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Licenciamento sanitário de serviços de hemoterapia',
                'codigo_procedimento' => '01.02.01.041-2',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Licenciamento sanitário de serviços de terapia renal substitutiva',
                'codigo_procedimento' => '01.02.01.044-7',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            
            // IV. INVESTIGAÇÃO
            [
                'descricao' => 'Investigação de surtos de doenças transmitida por alimentos',
                'codigo_procedimento' => '01.02.01.020-0',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Investigação de surtos de infecção em serviços de saúde',
                'codigo_procedimento' => '01.02.01.021-8',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Investigação de eventos adversos e/ou queixas técnicas',
                'codigo_procedimento' => '01.02.01.015-3',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            
            // V. ATIVIDADES EDUCATIVAS
            [
                'descricao' => 'Atividade educativa para a população',
                'codigo_procedimento' => '01.02.01.022-6',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Atividade educativa para o setor regulado',
                'codigo_procedimento' => '01.02.01.005-6',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Atividades educativas sobre a temática da dengue, realizadas para população',
                'codigo_procedimento' => '01.02.01.050-1',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Atividades educativas, com relação ao consumo de sódio, açúcar e gorduras, realizadas para o setor regulado e a população',
                'codigo_procedimento' => '01.02.01.051-0',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            
            // VI. SISTEMA DE GESTÃO DA QUALIDADE
            // PROCEDIMENTOS OPERACIONAIS PADRÃO (POP)
            [
                'descricao' => 'Implementação de Procedimentos Harmonizados em nível Tripartite relacionados a inspeção em estabelecimentos fabricantes de medicamentos',
                'codigo_procedimento' => '01.02.01.058-7',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Implementação de Procedimentos Harmonizados em nível Tripartite relacionados a inspeção em estabelecimentos fabricantes de insumos farmacêuticos',
                'codigo_procedimento' => '01.02.01.059-5',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Implementação de Procedimentos Harmonizados em nível Tripartite relacionados a inspeção em estabelecimentos fabricantes de produtos para saúde',
                'codigo_procedimento' => '01.02.01.060-9',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            
            // RELATÓRIOS DE INSPEÇÃO
            [
                'descricao' => 'Envio de Relatórios de Inspeção de estabelecimentos fabricantes de medicamentos à Anvisa',
                'codigo_procedimento' => '01.02.01.061-7',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Envio de Relatórios de Inspeção de estabelecimentos fabricantes de insumos farmacêuticos à Anvisa',
                'codigo_procedimento' => '01.02.01.062-5',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Envio de Relatórios de Inspeção de estabelecimentos fabricantes de produtos para saúde à Anvisa',
                'codigo_procedimento' => '01.02.01.063-3',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            
            // AUDITORIAS INTERNAS
            [
                'descricao' => 'Auditorias Internas realizadas no departamento responsável pelas atividades de inspeção de estabelecimentos fabricantes de medicamentos',
                'codigo_procedimento' => '01.02.01.064-1',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Auditorias Internas realizadas no departamento responsável pelas atividades de inspeção de estabelecimentos fabricantes de insumos farmacêuticos',
                'codigo_procedimento' => '01.02.01.065-0',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Auditorias Internas realizadas no departamento responsável pelas atividades de inspeção de estabelecimentos fabricantes de produtos para saúde',
                'codigo_procedimento' => '01.02.01.066-8',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            
            // VII. OUTROS
            [
                'descricao' => 'Análise de projetos básicos de arquitetura',
                'codigo_procedimento' => '01.02.01.006-4',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Aprovação de projetos básicos de arquitetura',
                'codigo_procedimento' => '01.02.01.019-6',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Recebimento de denúncias/ reclamações',
                'codigo_procedimento' => '01.02.01.023-4',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Atendimento a denúncias/ reclamações',
                'codigo_procedimento' => '01.02.01.024-2',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Instauração de processo administrativo sanitário',
                'codigo_procedimento' => '01.02.01.052-8',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Conclusão de processo administrativo sanitário',
                'codigo_procedimento' => '01.02.01.053-6',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Fiscalização do uso de produtos fumígenos derivados do tabaco em ambientes coletivos fechados, públicos ou privados',
                'codigo_procedimento' => '01.02.01.048-0',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ],
            [
                'descricao' => 'Laudo de análise laboratorial do programa de monitoramento de alimentos recebidos pela vigilância sanitária',
                'codigo_procedimento' => '01.02.01.049-8',
                'atividade_sia' => true,
                'competencia' => 'ambos',
                'ativo' => true
            ]
        ];

        // Inserir os dados na tabela
        foreach ($tiposAcoes as $tipoAcao) {
            DB::table('tipo_acoes')->updateOrInsert(
                ['codigo_procedimento' => $tipoAcao['codigo_procedimento']],
                $tipoAcao
            );
        }
    }
}
