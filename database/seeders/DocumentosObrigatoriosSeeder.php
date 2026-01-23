<?php

namespace Database\Seeders;

use App\Models\TipoDocumentoObrigatorio;
use Illuminate\Database\Seeder;

class DocumentosObrigatoriosSeeder extends Seeder
{
    /**
     * Seed dos tipos de documentos obrigatórios baseado no instrutivo da VISA.
     */
    public function run(): void
    {
        // =====================================================
        // DOCUMENTOS COMUNS A TODOS OS SERVIÇOS
        // =====================================================
        $documentosComuns = [
            [
                'nome' => 'CNPJ',
                'nomenclatura_arquivo' => 'CNPJ',
                'descricao' => 'Cadastro Nacional de Pessoa Jurídica',
                'instrucoes' => 'Fazer o download do site da Receita Federal - com data de emissão de até 30 dias.',
                'url_referencia' => 'http://servicos.receita.fazenda.gov.br/Servicos/cnpjreva/Cnpjreva_Solicitacao.asp',
                'documento_comum' => true,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'prazo_validade_dias' => 30,
                'ordem' => 1,
            ],
            [
                'nome' => 'Contrato Social',
                'nomenclatura_arquivo' => 'CONTRATO SOCIAL',
                'descricao' => 'Contrato Social da empresa - versão que identifica o responsável legal pela empresa',
                'instrucoes' => 'Versão que identifica o responsável legal pela empresa. APENAS PARA EMPRESAS PRIVADAS.',
                'documento_comum' => true,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'privado',
                'observacao_publica' => 'Não se aplica a estabelecimentos públicos',
                'ordem' => 2,
            ],
            [
                'nome' => 'DARE',
                'nomenclatura_arquivo' => 'DARE',
                'descricao' => 'Documento de Arrecadação Estadual - CÓDIGO 420',
                'instrucoes' => 'Documento de Arrecadação Estadual CÓDIGO 420 – SUBCÓDIGO: ver tabela de taxas.',
                'url_referencia' => 'https://www.sefaz.to.gov.br/dare/servlet/hnetccwkda',
                'documento_comum' => true,
                'escopo_competencia' => 'estadual',
                'tipo_setor' => 'privado',
                'observacao_publica' => 'Isento para estabelecimentos públicos',
                'ordem' => 3,
            ],
            [
                'nome' => 'Comprovante de Pagamento do DARE',
                'nomenclatura_arquivo' => 'COMP PAGAMENTO',
                'descricao' => 'Comprovante de pagamento do DARE',
                'instrucoes' => 'Comprovante de pagamento do Documento de Arrecadação Estadual.',
                'documento_comum' => true,
                'escopo_competencia' => 'estadual',
                'tipo_setor' => 'privado',
                'observacao_publica' => 'Isento para estabelecimentos públicos',
                'ordem' => 4,
            ],
            [
                'nome' => 'Parecer do Projeto Arquitetônico',
                'nomenclatura_arquivo' => 'PARECER PROJETO',
                'descricao' => 'Parecer técnico de análise do projeto arquitetônico do estabelecimento',
                'instrucoes' => 'Parecer técnico de análise do projeto arquitetônico do estabelecimento ou comprovação que o projeto está em análise.',
                'documento_comum' => true,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 5,
            ],
        ];

        // =====================================================
        // DOCUMENTOS ESPECÍFICOS POR ATIVIDADE
        // =====================================================
        $documentosEspecificos = [
            // Documentos de Localização e RT
            [
                'nome' => 'Alvará de Localização',
                'nomenclatura_arquivo' => 'ALVARA LOC',
                'descricao' => 'Alvará de localização emitido pela prefeitura municipal',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 10,
            ],
            [
                'nome' => 'Certificado de Responsabilidade Técnica',
                'nomenclatura_arquivo' => 'CERT RT',
                'descricao' => 'Certificado de responsabilidade técnica pelo estabelecimento (emitido pelo conselho de classe)',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 11,
            ],
            
            // Documentos de Saúde
            [
                'nome' => 'Cadastro no NOTIVISA',
                'nomenclatura_arquivo' => 'CAD NOTIVISA',
                'descricao' => 'Print da tela que comprova o cadastro no Notivisa',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 12,
            ],
            [
                'nome' => 'CNES',
                'nomenclatura_arquivo' => 'CNES',
                'descricao' => 'Cadastro Nacional de Estabelecimento de Saúde - Ficha de Estabelecimento Identificação atualizada',
                'instrucoes' => 'Ficha de Estabelecimento Identificação atualizada gerada no site do CNES.',
                'url_referencia' => 'https://cnes.datasus.gov.br/pages/estabelecimentos/ficha/',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 13,
            ],
            [
                'nome' => 'Relação de Equipamentos de Radiação Ionizante',
                'nomenclatura_arquivo' => 'REL EQUIPAMENTOS',
                'descricao' => 'Relação dos equipamentos que emitem radiação ionizante (Rx, mamografia, tomografia, etc.)',
                'instrucoes' => 'Se não possuir, faça uma declaração informando que não possui equipamento que emita radiação ionizante.',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 14,
            ],
            
            // Documentos de Radiologia
            [
                'nome' => 'Planilha Síntese - Mamografia',
                'nomenclatura_arquivo' => 'PLANILHA SINTESE',
                'descricao' => 'Planilha síntese dos testes de controle de qualidade em mamografia',
                'instrucoes' => 'Caso o estabelecimento possua o serviço de mamografia deverá encaminhar a planilha síntese preenchida.',
                'url_referencia' => 'https://www.gov.br/anvisa/pt-br/assuntos/servicosdesaude/projeto-de-melhoria-do-processo-de-inspecao-sanitaria-em-servicos-de-saude-e-de-interesse-para-a-saude/harmonizacao-de-roteiros-objetivos-de-inspecao-roi/confira-os-materiais-disponibilizados-roi-planilhas-sintese-e-links-de-limesurvey',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 15,
            ],
            [
                'nome' => 'Planilha Síntese - Radiologia Intervencionista',
                'nomenclatura_arquivo' => 'PLANILHA SINTESE',
                'descricao' => 'Planilha síntese dos testes de controle de qualidade em radiologia intervencionista',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 16,
            ],
            [
                'nome' => 'Planilha Síntese - Radiografia Médica',
                'nomenclatura_arquivo' => 'PLANILHA SINTESE',
                'descricao' => 'Planilha síntese dos testes de controle de qualidade em radiografia médica',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 17,
            ],
            
            // Documentos ANVISA
            [
                'nome' => 'AFE - ANVISA',
                'nomenclatura_arquivo' => 'AFE – ANVISA',
                'descricao' => 'Autorização de funcionamento de empresa emitida pela Anvisa',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 20,
            ],
            [
                'nome' => 'AE - ANVISA',
                'nomenclatura_arquivo' => 'AE – ANVISA',
                'descricao' => 'Autorização especial emitida pela Anvisa (para substâncias sujeitas a controle especial)',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 21,
            ],
            
            // Documentos de Produtos
            [
                'nome' => 'Relação de Produtos',
                'nomenclatura_arquivo' => 'REL DE PRODUTOS',
                'descricao' => 'Relação sucinta da natureza e espécies dos produtos com que a empresa irá trabalhar',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 22,
            ],
            [
                'nome' => 'Relação de Alimentos',
                'nomenclatura_arquivo' => 'REL ALIMENTOS',
                'descricao' => 'Relação dos tipos de alimentos que vai produzir',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 23,
            ],
            [
                'nome' => 'Relação de Atividades',
                'nomenclatura_arquivo' => 'REL ATIVIDADES',
                'descricao' => 'Relação sucinta das atividades desenvolvidas pela empresa',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 24,
            ],
            
            // Documentos Ambientais
            [
                'nome' => 'Licença Ambiental',
                'nomenclatura_arquivo' => 'LICENCA AMBIENTAL',
                'descricao' => 'Licença do órgão ambiental',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 25,
            ],
            
            // Documentos de Laboratório
            [
                'nome' => 'Declaração LACEN',
                'nomenclatura_arquivo' => 'DEC LACEN',
                'descricao' => 'Declaração de inscrição no programa de controle de qualidade emitido pelo LACEN',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 30,
            ],
            [
                'nome' => 'Relação de Exames',
                'nomenclatura_arquivo' => 'REL EXAMES',
                'descricao' => 'Relação dos exames realizados',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 31,
            ],
            [
                'nome' => 'Relação de Postos de Coleta',
                'nomenclatura_arquivo' => 'REL POSTO COLETA',
                'descricao' => 'Relação de posto(s) de coleta vinculado(s) ao laboratório contendo nome, CNPJ, município e endereço',
                'instrucoes' => 'Se o laboratório não possuir postos de coleta deve fazer uma declaração informando que não possui postos de coleta. Esta declaração deve ser assinada pelo responsável legal.',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 32,
            ],
            
            // Documentos de Medicina Nuclear e Radioterapia
            [
                'nome' => 'Autorização CNEN',
                'nomenclatura_arquivo' => 'CNEN',
                'descricao' => 'Autorização de operação (funcionamento) emitida pelo CNEN',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 40,
            ],
            [
                'nome' => 'Declaração RT Físico',
                'nomenclatura_arquivo' => 'DEC RT FISICO',
                'descricao' => 'Declaração de responsabilidade técnica pelo setor de proteção radiológica (físico) e seu substituto',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 41,
            ],
            [
                'nome' => 'Título de Especialista em Física Médica',
                'nomenclatura_arquivo' => 'TITULO FISICO',
                'descricao' => 'Título de especialista em física médica pela ABFM',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 42,
            ],
            [
                'nome' => 'Certidão RT Substituto Médico',
                'nomenclatura_arquivo' => 'CERT RT SUBSTITUTO MÉDICO',
                'descricao' => 'Certidão de Responsabilidade Técnica do substituto do RT médico',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 43,
            ],
            [
                'nome' => 'Certidão RT Substituto Enfermeiro',
                'nomenclatura_arquivo' => 'CERT RT SUBSTITUTO ENFERMEIRO',
                'descricao' => 'Certidão de Responsabilidade Técnica do substituto do RT enfermeiro',
                'documento_comum' => false,
                'escopo_competencia' => 'todos',
                'tipo_setor' => 'todos',
                'ordem' => 44,
            ],
        ];

        // Inserir documentos comuns
        foreach ($documentosComuns as $doc) {
            TipoDocumentoObrigatorio::updateOrCreate(
                ['nome' => $doc['nome']],
                array_merge($doc, ['ativo' => true])
            );
        }

        // Inserir documentos específicos
        foreach ($documentosEspecificos as $doc) {
            TipoDocumentoObrigatorio::updateOrCreate(
                ['nome' => $doc['nome']],
                array_merge($doc, ['ativo' => true])
            );
        }

        $this->command->info('Documentos obrigatórios criados/atualizados com sucesso!');
        $this->command->info('- ' . count($documentosComuns) . ' documentos comuns');
        $this->command->info('- ' . count($documentosEspecificos) . ' documentos específicos');
    }
}
