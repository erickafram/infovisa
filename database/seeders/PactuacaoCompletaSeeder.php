<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pactuacao;

/**
 * Seeder completo de Pactuação conforme regras do InfoVisa 3.0
 * 
 * REGRAS IMPLEMENTADAS:
 * 
 * TABELA I - Municipal:
 *   - tipo_questionario = 'risco': SIM=Alto, NÃO=Médio (competência sempre municipal)
 *   - tipo_questionario = 'localizacao': Se em hospital = Estadual (exceto Palmas/Araguaína)
 *   - tipo_questionario = 'risco_localizacao': Pergunta1=Risco, Pergunta2=Localização
 * 
 * TABELA II - Estadual Exclusiva (sempre estadual, sempre alto risco)
 * 
 * TABELA III - Alto Risco Pactuado (estadual com descentralização)
 * 
 * TABELA IV - Com Questionário:
 *   - tipo_questionario = 'competencia': SIM=Estadual, NÃO=Municipal
 *   - tipo_questionario = 'risco': SIM=Alto=Estadual, NÃO=Médio=Municipal
 *   - tipo_questionario = 'competencia_localizacao': Múltiplas perguntas
 * 
 * TABELA V - Definir VISA:
 *   - SIM = Sujeito à VISA (estadual com descentralização)
 *   - NÃO = Não sujeito ao licenciamento sanitário
 */
class PactuacaoCompletaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== INICIANDO SEED DE PACTUAÇÃO COMPLETA ===');
        
        // NÃO limpa a tabela - apenas adiciona/atualiza
        
        $this->seedTabelaI_Risco();
        $this->seedTabelaI_Localizacao();
        $this->seedTabelaI_RiscoLocalizacao();
        $this->seedTabelaIII_Complexos();
        $this->seedTabelaIV_Laboratorios();
        
        $this->command->info('=== SEED DE PACTUAÇÃO COMPLETA FINALIZADO ===');
    }

    private function normalizarCnae($cnae)
    {
        return preg_replace('/[^0-9]/', '', $cnae);
    }

    /**
     * TABELA I - CNAEs com questionário que define RISCO (competência sempre municipal)
     * SIM = Alto Risco | NÃO = Médio Risco
     */
    private function seedTabelaI_Risco()
    {
        $this->command->info('Tabela I - CNAEs com questionário de RISCO...');
        
        $dados = [
            [
                'cnae' => '1063-5/00',
                'descricao' => 'Fabricação de farinha de mandioca e derivados',
                'pergunta' => 'O resultado do exercício da atividade econômica será diferente de produto artesanal?',
            ],
            [
                'cnae' => '1071-6/00',
                'descricao' => 'Fabricação de açúcar em bruto',
                'pergunta' => 'O resultado do exercício da atividade econômica será diferente de produto artesanal?',
            ],
            [
                'cnae' => '1099-6/04',
                'descricao' => 'Fabricação de gelo comum',
                'pergunta' => 'O gelo fabricado será para consumo humano ou entrará em contato com alimentos e bebidas?',
            ],
            [
                'cnae' => '1081-3/01',
                'descricao' => 'Beneficiamento de café',
                'pergunta' => 'O resultado do exercício da atividade econômica será diferente de produto artesanal?',
            ],
            [
                'cnae' => '9602-5/02',
                'descricao' => 'Atividades de estética e outros serviços de cuidados com a beleza',
                'pergunta' => 'Haverá no exercício da atividade a realização de procedimentos invasivos?',
            ],
            [
                'cnae' => '9609-2/99',
                'descricao' => 'Outras atividades de serviços pessoais não especificadas anteriormente',
                'pergunta' => 'Haverá no exercício da atividade a realização de procedimentos invasivos?',
            ],
            [
                'cnae' => '4632-0/03',
                'descricao' => 'Comércio atacadista de cereais e leguminosas beneficiados, farinhas, amidos e féculas, com atividade de fracionamento e acondicionamento associada',
                'pergunta' => 'Haverá no exercício da atividade a realização de fracionamento, acondicionamento, embalagem e/ou rotulagem, consideradas etapas do processo produtivo?',
            ],
            [
                'cnae' => '7500-1/00',
                'descricao' => 'Atividades veterinárias',
                'pergunta' => 'O resultado do exercício da atividade incluirá a comercialização e/ou uso de medicamentos controlados e/ou equipamentos de diagnóstico por imagem?',
            ],
            [
                'cnae' => '8630-5/03',
                'descricao' => 'Atividade médica ambulatorial restrita a consultas',
                'pergunta' => 'Haverá no exercício da atividade a realização de procedimentos invasivos?',
            ],
            [
                'cnae' => '8630-5/99',
                'descricao' => 'Atividades de atenção ambulatorial não especificadas anteriormente',
                'pergunta' => 'Haverá no exercício da atividade a realização de procedimentos invasivos?',
            ],
            [
                'cnae' => '8650-0/01',
                'descricao' => 'Atividades de enfermagem',
                'pergunta' => 'Haverá no exercício da atividade a realização de procedimentos invasivos?',
            ],
            [
                'cnae' => '8650-0/99',
                'descricao' => 'Atividades de profissionais da área de saúde não especificadas anteriormente',
                'pergunta' => 'Haverá no exercício da atividade a realização de procedimentos invasivos?',
            ],
            [
                'cnae' => '8690-9/99',
                'descricao' => 'Outras atividades de atenção à saúde humana não especificadas anteriormente',
                'pergunta' => 'Haverá no exercício da atividade a realização de procedimentos invasivos?',
            ],
        ];

        foreach ($dados as $item) {
            Pactuacao::updateOrCreate(
                ['cnae_codigo' => $this->normalizarCnae($item['cnae'])],
                [
                    'tipo' => 'municipal',
                    'tabela' => 'I',
                    'cnae_descricao' => $item['descricao'],
                    'classificacao_risco' => 'medio', // Default
                    'requer_questionario' => true,
                    'tipo_questionario' => 'risco',
                    'pergunta' => $item['pergunta'],
                    'risco_sim' => 'alto',
                    'risco_nao' => 'medio',
                    'competencia_base' => 'municipal',
                    'observacao' => 'Competência sempre MUNICIPAL. Risco: SIM=Alto, NÃO=Médio',
                    'ativo' => true,
                ]
            );
        }
        
        $this->command->info('  -> ' . count($dados) . ' CNAEs processados');
    }

    /**
     * TABELA I - CNAEs com questionário de LOCALIZAÇÃO (hospital)
     * Se em hospital = Estadual (exceto Palmas e Araguaína)
     */
    private function seedTabelaI_Localizacao()
    {
        $this->command->info('Tabela I - CNAEs com questionário de LOCALIZAÇÃO...');
        
        $dados = [
            [
                'cnae' => '5620-1/01',
                'descricao' => 'Fornecimento de alimentos preparados preponderantemente para empresas',
                'pergunta' => 'O estabelecimento exerce a atividade dentro de Unidade Hospitalar?',
                'risco' => 'alto',
            ],
        ];

        foreach ($dados as $item) {
            Pactuacao::updateOrCreate(
                ['cnae_codigo' => $this->normalizarCnae($item['cnae'])],
                [
                    'tipo' => 'municipal',
                    'tabela' => 'I',
                    'cnae_descricao' => $item['descricao'],
                    'classificacao_risco' => $item['risco'],
                    'requer_questionario' => true,
                    'tipo_questionario' => 'localizacao',
                    'pergunta' => $item['pergunta'],
                    'competencia_base' => 'municipal',
                    'municipios_excecao_hospitalar' => ['Palmas', 'Araguaína'],
                    'observacao' => 'Competência Municipal. Se em hospital = Estadual (exceto Palmas e Araguaína)',
                    'ativo' => true,
                ]
            );
        }
        
        $this->command->info('  -> ' . count($dados) . ' CNAEs processados');
    }

    /**
     * TABELA I - CNAEs com questionário de RISCO + LOCALIZAÇÃO
     * Pergunta 1: Define risco (SIM=Alto, NÃO=Médio)
     * Pergunta 2: Define competência (Se hospital = Estadual, exceto Palmas/Araguaína)
     */
    private function seedTabelaI_RiscoLocalizacao()
    {
        $this->command->info('Tabela I - CNAEs com RISCO + LOCALIZAÇÃO...');
        
        $dados = [
            [
                'cnae' => '9601-7/01',
                'descricao' => 'Lavanderias',
                'pergunta' => 'O exercício da atividade compreenderá lavanderia, autônoma e independente de outro estabelecimento, que processa roupa hospitalar?',
                'pergunta2' => 'O estabelecimento exerce a atividade dentro de Unidade Hospitalar?',
            ],
        ];

        foreach ($dados as $item) {
            Pactuacao::updateOrCreate(
                ['cnae_codigo' => $this->normalizarCnae($item['cnae'])],
                [
                    'tipo' => 'municipal',
                    'tabela' => 'I',
                    'cnae_descricao' => $item['descricao'],
                    'classificacao_risco' => 'medio', // Default
                    'requer_questionario' => true,
                    'tipo_questionario' => 'risco_localizacao',
                    'pergunta' => $item['pergunta'],
                    'pergunta2' => $item['pergunta2'],
                    'tipo_pergunta2' => 'localizacao',
                    'risco_sim' => 'alto',
                    'risco_nao' => 'medio',
                    'competencia_base' => 'municipal',
                    'municipios_excecao_hospitalar' => ['Palmas', 'Araguaína'],
                    'observacao' => 'Pergunta 1: Risco (SIM=Alto, NÃO=Médio). Pergunta 2: Se hospital = Estadual (exceto Palmas/Araguaína)',
                    'ativo' => true,
                ]
            );
        }
        
        $this->command->info('  -> ' . count($dados) . ' CNAEs processados');
    }

    /**
     * TABELA III - CNAEs com regras complexas de descentralização
     */
    private function seedTabelaIII_Complexos()
    {
        $this->command->info('Tabela III - CNAEs com regras complexas...');
        
        // 8640-2/05 - Diagnóstico por imagem com radiação ionizante
        // Regra MUITO complexa - depende do tipo de exame E do município
        Pactuacao::updateOrCreate(
            ['cnae_codigo' => $this->normalizarCnae('8640-2/05')],
            [
                'tipo' => 'estadual',
                'tabela' => 'III',
                'cnae_descricao' => 'Serviços de diagnóstico por imagem com uso de radiação ionizante, exceto tomografia',
                'classificacao_risco' => 'alto',
                'requer_questionario' => true,
                'tipo_questionario' => 'competencia',
                'pergunta' => 'O estabelecimento realiza APENAS radiologia médica/odontológica e/ou densitometria óssea? (Se SIM = pode ser municipal em alguns casos)',
                'municipios_excecao' => ['Palmas'],
                'observacao' => 'REGRA COMPLEXA: Palmas=Municipal para todos. Araguaína=Municipal exceto medicina nuclear. Gurupi/Paraíso/Porto Nacional=Municipal apenas para radiologia e densitometria.',
                'ativo' => true,
            ]
        );

        // 8690-9/02 - Banco de leite humano
        Pactuacao::updateOrCreate(
            ['cnae_codigo' => $this->normalizarCnae('8690-9/02')],
            [
                'tipo' => 'estadual',
                'tabela' => 'III',
                'cnae_descricao' => 'Atividades de banco de leite humano',
                'classificacao_risco' => 'alto',
                'requer_questionario' => true,
                'tipo_questionario' => 'competencia',
                'pergunta' => 'O estabelecimento é privado ou da rede pública municipal?',
                'municipios_excecao' => ['Araguaína'],
                'observacao' => 'Estadual, exceto Araguaína quando estabelecimentos privados ou da rede pública municipal',
                'ativo' => true,
            ]
        );

        // 8610-1/01 - Atendimento hospitalar
        Pactuacao::updateOrCreate(
            ['cnae_codigo' => $this->normalizarCnae('8610-1/01')],
            [
                'tipo' => 'estadual',
                'tabela' => 'III',
                'cnae_descricao' => 'Atividades de atendimento hospitalar, exceto pronto-socorro e unidades para atendimento a urgências',
                'classificacao_risco' => 'alto',
                'requer_questionario' => true,
                'tipo_questionario' => 'competencia',
                'pergunta' => 'O estabelecimento é privado ou da rede pública municipal?',
                'municipios_excecao' => ['Araguaína'],
                'observacao' => 'Estadual, exceto Araguaína quando estabelecimentos privados ou da rede pública municipal',
                'ativo' => true,
            ]
        );

        // 8610-1/02 - Pronto-socorro (atualizar)
        Pactuacao::updateOrCreate(
            ['cnae_codigo' => $this->normalizarCnae('8610-1/02')],
            [
                'tipo' => 'estadual',
                'tabela' => 'III',
                'cnae_descricao' => 'Atividades de atendimento em pronto-socorro e unidades hospitalares para atendimento a urgências',
                'classificacao_risco' => 'alto',
                'requer_questionario' => true,
                'tipo_questionario' => 'competencia',
                'pergunta' => 'O estabelecimento é da rede pública municipal?',
                'municipios_excecao' => ['Araguaína', 'Palmas', 'Gurupi', 'Porto Nacional'],
                'observacao' => 'Estadual. Araguaína=Municipal (privados e rede pública municipal). Palmas/Gurupi/Porto Nacional=Municipal apenas rede pública municipal',
                'ativo' => true,
            ]
        );
        
        $this->command->info('  -> 4 CNAEs processados');
    }

    /**
     * TABELA IV - Laboratórios clínicos com regra de localização
     */
    private function seedTabelaIV_Laboratorios()
    {
        $this->command->info('Tabela IV - Laboratórios clínicos...');
        
        Pactuacao::updateOrCreate(
            ['cnae_codigo' => $this->normalizarCnae('8640-2/02')],
            [
                'tipo' => 'estadual',
                'tabela' => 'IV',
                'cnae_descricao' => 'Laboratórios clínicos',
                'classificacao_risco' => 'alto',
                'requer_questionario' => true,
                'tipo_questionario' => 'competencia_localizacao',
                'pergunta' => 'O estabelecimento realiza análises clínicas?',
                'pergunta2' => 'O estabelecimento exerce a atividade dentro de Unidade Hospitalar?',
                'tipo_pergunta2' => 'localizacao',
                'municipios_excecao' => ['Araguaína', 'Guaraí', 'Gurupi', 'Palmas', 'Porto Nacional', 'Paraíso do Tocantins'],
                'municipios_excecao_hospitalar' => ['Palmas', 'Araguaína'],
                'observacao' => 'Perg1: Análises clínicas? SIM=Estadual (exceto descentralizados), NÃO=Municipal (posto de coleta). Perg2: Hospital? SIM=Estadual (exceto Palmas/Araguaína)',
                'ativo' => true,
            ]
        );
        
        $this->command->info('  -> 1 CNAE processado');
    }
}
