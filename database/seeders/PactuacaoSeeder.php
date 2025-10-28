<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pactuacao;
use Illuminate\Support\Facades\DB;

class PactuacaoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar tabela antes de popular
        Pactuacao::query()->delete();

        $this->seedTabelaI();
        $this->seedTabelaII();
        $this->seedTabelaIII();
        $this->seedTabelaIV();
        $this->seedTabelaV();

        $this->command->info('Pactuações importadas com sucesso!');
    }

    /**
     * Normaliza código CNAE removendo formatação (pontos, traços, barras)
     */
    private function normalizarCnae($cnae)
    {
        return preg_replace('/[^0-9]/', '', $cnae);
    }

    private function seedTabelaI()
    {
        $this->command->info('Importando Tabela I - Atividades Municipais (139 municípios)...');
        
        $dados = [
            ['1041-4/00', 'Fabricação de óleos vegetais em bruto, exceto óleo de milho', 'baixo'],
            ['1065-1/02', 'Fabricação de óleo de milho em bruto', 'baixo'],
            ['1081-3/02', 'Torrefação e moagem de café', 'baixo'],
            ['1063-5/00', 'Fabricação de farinha de mandioca e derivados', 'baixo'],
            ['1071-6/00', 'Fabricação de açúcar em bruto', 'medio'],
            ['1099-6/04', 'Fabricação de gelo comum', 'baixo'],
            ['4771-7/01', 'Comércio varejista de produtos farmacêuticos, sem manipulação de fórmulas (drogaria)', 'medio'],
            ['5620-1/01', 'Fornecimento de alimentos preparados preponderantemente para empresas', 'medio'],
            ['8630-5/04', 'Atividade odontológica', 'medio'],
            ['8640-2/08', 'Serviços de diagnóstico por registro gráfico - ECG, EEG e outros exames análogos', 'medio'],
            ['9603-3/05', 'Serviços de somatoconservação', 'baixo'],
            ['9609-2/06', 'Serviços de tatuagem e colocação de piercing', 'baixo'],
            ['1091-1/02', 'Fabricação de produtos de padaria e confeitaria com predominância de produção própria', 'baixo'],
            ['3250-7/06', 'Serviços de prótese dentária', 'medio'],
            ['5611-2/01', 'Restaurantes e similares', 'medio'],
            ['5611-2/02', 'Bares e outros estabelecimentos especializados em servir bebidas', 'medio'],
            ['5611-2/03', 'Lanchonetes, casas de chá, de sucos e similares', 'medio'],
            ['8122-2/00', 'Imunização e controle de pragas urbanas', 'medio'],
            ['8511-2/00', 'Educação infantil - creche', 'medio'],
            ['9602-5/01', 'Cabeleireiros, manicure e pedicure', 'baixo'],
        ];

        foreach ($dados as $item) {
            Pactuacao::create([
                'tipo' => 'municipal',
                'tabela' => 'I',
                'cnae_codigo' => $this->normalizarCnae($item[0]),
                'cnae_descricao' => $item[1],
                'classificacao_risco' => $item[2],
                'requer_questionario' => false,
                'municipio' => null, // Tabela I é para TODOS os municípios
                'observacao' => 'Atividade de competência municipal (139 municípios do Tocantins)',
                'ativo' => true,
            ]);
        }
    }

    private function seedTabelaII()
    {
        $this->command->info('Importando Tabela II - Atividades Estaduais Exclusivas...');
        
        $dados = [
            ['1099-6/03', 'Fabricação de fermentos e leveduras'],
            ['1099-6/06', 'Fabricação de adoçantes naturais e artificiais'],
            ['1099-6/07', 'Fabricação de alimentos dietéticos e complementos alimentares'],
            ['2052-5/00', 'Fabricação de desinfestantes domissanitários'],
            ['2061-4/00', 'Fabricação de sabões e detergentes sintéticos'],
            ['2063-1/00', 'Fabricação de cosméticos, produtos de perfumaria e de higiene pessoal'],
            ['2110-6/00', 'Fabricação de produtos farmoquímicos'],
            ['2121-1/01', 'Fabricação de medicamentos alopáticos para uso humano'],
            ['2121-1/02', 'Fabricação de medicamentos homeopáticos para uso humano'],
            ['4771-7/02', 'Comércio varejista de produtos farmacêuticos, com manipulação de fórmulas'],
            ['8640-2/03', 'Serviços de diálise e nefrologia'],
            ['8640-2/10', 'Serviços de quimioterapia'],
        ];

        foreach ($dados as $item) {
            Pactuacao::create([
                'tipo' => 'estadual',
                'tabela' => 'II',
                'cnae_codigo' => $this->normalizarCnae($item[0]),
                'cnae_descricao' => $item[1],
                'classificacao_risco' => 'alto',
                'requer_questionario' => false,
                'ativo' => true,
            ]);
        }
    }

    private function seedTabelaIII()
    {
        $this->command->info('Importando Tabela III - Atividades Alto Risco Pactuadas...');
        
        $dados = [
            ['0892-4/03', 'Refino e outros tratamentos do sal', ['Palmas']],
            ['1032-5/01', 'Fabricação de conservas de palmito', ['Palmas']],
            ['1042-2/00', 'Fabricação de óleos vegetais refinados, exceto óleo de milho', ['Palmas']],
            ['1053-8/00', 'Fabricação de sorvetes e outros gelados comestíveis', ['Palmas']],
            ['1121-6/00', 'Fabricação de águas envasadas', ['Palmas']],
            ['4644-3/01', 'Comércio atacadista de medicamentos e drogas de uso humano', ['Araguaína', 'Augustinópolis', 'Gurupi', 'Palmas', 'Paraíso do TO', 'Porto Nacional']],
            ['8610-1/02', 'Atividades de atendimento em pronto-socorro e unidades hospitalares para atendimento a urgências', ['Araguaína', 'Palmas', 'Gurupi', 'Porto Nacional']],
        ];

        foreach ($dados as $item) {
            Pactuacao::create([
                'tipo' => 'estadual',
                'tabela' => 'III',
                'cnae_codigo' => $this->normalizarCnae($item[0]),
                'cnae_descricao' => $item[1],
                'municipios_excecao' => $item[2],
                'classificacao_risco' => 'alto',
                'requer_questionario' => false,
                'observacao' => 'Atividade de alto risco pactuada com municípios específicos',
                'ativo' => true,
            ]);
        }
    }

    private function seedTabelaIV()
    {
        $this->command->info('Importando Tabela IV - Atividades com Questionário...');
        
        $dados = [
            ['1031-7/00', 'Fabricação de conservas de frutas', 'O resultado do exercício da atividade será diferente de produto artesanal?', ['Araguaína', 'Augustinópolis', 'Colinas do TO', 'Dianópolis', 'Guaraí', 'Gurupi', 'Palmas', 'Porto Nacional', 'Paraíso do TO', 'Tocantinópolis']],
            ['1061-9/01', 'Beneficiamento de arroz', 'O beneficiamento do produto será industrial?', ['Araguaína', 'Augustinópolis', 'Colinas do TO', 'Dianópolis', 'Guaraí', 'Gurupi', 'Palmas', 'Porto Nacional', 'Paraíso do TO', 'Tocantinópolis']],
            ['1092-9/00', 'Fabricação de biscoitos e bolachas', 'O resultado do exercício da atividade será diferente de produto artesanal?', ['Araguaína', 'Augustinópolis', 'Colinas do TO', 'Dianópolis', 'Guaraí', 'Gurupi', 'Palmas', 'Porto Nacional', 'Paraíso do TO', 'Tocantinópolis']],
            ['1094-5/00', 'Fabricação de massas alimentícias', 'O resultado do exercício da atividade será diferente de produto artesanal?', ['Palmas']],
            ['4771-7/03', 'Comércio varejista de produtos farmacêuticos homeopáticos', 'Haverá manipulação de produtos farmacêuticos homeopáticos?', []],
            ['8640202', 'Laboratórios clínicos', 'O estabelecimento realiza análises clínicas? (Compete ao município quando o estabelecimento realiza somente atividade de posto de coleta laboratorial)', ['Araguaína', 'Guaraí', 'Gurupi', 'Palmas', 'Porto Nacional', 'Paraíso do TO']],
        ];

        foreach ($dados as $item) {
            Pactuacao::create([
                'tipo' => 'estadual',
                'tabela' => 'IV',
                'cnae_codigo' => $this->normalizarCnae($item[0]),
                'cnae_descricao' => $item[1],
                'pergunta' => $item[2],
                'municipios_excecao' => $item[3],
                'classificacao_risco' => 'medio', // Depende da resposta
                'requer_questionario' => true,
                'observacao' => 'Resposta SIM = Estadual (exceto municípios descentralizados) | Resposta NÃO = Municipal',
                'ativo' => true,
            ]);
        }
    }

    private function seedTabelaV()
    {
        $this->command->info('Importando Tabela V - Atividades que dependem de questionário para definir se é VISA...');
        
        $dados = [
            ['3291-4/00', 'Fabricação de escovas, pincéis e vassouras', 'Haverá no exercício a fabricação de escova dental?'],
            ['1043-1/00', 'Fabricação de margarina e outras gorduras vegetais e de óleos não-comestíveis de animais', 'O produto fabricado será comestível?'],
            ['2014-2/00', 'Fabricação de gases industriais', 'O gás fabricado será usado para fins terapêuticos e/ou para gaseificação de bebidas?'],
            ['2219-6/00', 'Fabricação de artefatos de borracha não especificados anteriormente', 'Haverá a fabricação de preservativos ou luvas para procedimentos médicos?'],
            ['3250-7/07', 'Fabricação de artigos ópticos', 'Haverá fabricação de produto para saúde?'],
        ];

        foreach ($dados as $item) {
            Pactuacao::create([
                'tipo' => 'estadual',
                'tabela' => 'V',
                'cnae_codigo' => $this->normalizarCnae($item[0]),
                'cnae_descricao' => $item[1],
                'pergunta' => $item[2],
                'classificacao_risco' => 'medio', // Depende da resposta
                'requer_questionario' => true,
                'observacao' => 'Resposta SIM = Sujeito à VISA (aplicar regras de competência) | Resposta NÃO = NÃO sujeito à VISA',
                'ativo' => true,
            ]);
        }
    }
}
