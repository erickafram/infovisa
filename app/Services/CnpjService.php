<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CnpjService
{
    private const TIMEOUT = 30; // 30 segundos
    
    // URLs das APIs
    private const MINHA_RECEITA_URL = 'https://minhareceita.org';
    private const BRASIL_API_URL = 'https://brasilapi.com.br/api/cnpj/v1';
    private const RECEITA_WS_URL = 'https://www.receitaws.com.br/v1/cnpj';

    /**
     * Cliente HTTP padronizado para consultas externas.
     *
     * Em ambiente local desabilita verificação SSL para evitar erros de certificado
     * (cURL error 60 em máquinas de desenvolvimento sem cadeia de CA configurada).
     */
    private function httpClient(): PendingRequest
    {
        return Http::timeout(self::TIMEOUT)
            ->withOptions([
                'verify' => !app()->environment('local'),
            ]);
    }

    /**
     * Consulta dados de CNPJ com fallback em múltiplas APIs
     *
     * @param string $cnpj
     * @return array|null
     */
    public function consultarCnpj(string $cnpj): ?array
    {
        try {
            // Remove formatação do CNPJ
            $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);
            
            // Valida se tem 14 dígitos
            if (strlen($cnpjLimpo) !== 14) {
                throw new Exception('CNPJ deve ter 14 dígitos');
            }

            // Tenta API 1: Minha Receita (principal)
            Log::info('Tentando buscar CNPJ na Receita', ['cnpj' => $cnpjLimpo]);
            $dados = $this->consultarMinhaReceita($cnpjLimpo);
            if ($dados !== null) {
                Log::info('CNPJ encontrado na Receita', ['cnpj' => $cnpjLimpo]);
                return $dados;
            }

            // Tenta API 2: BrasilAPI (backup 1)
            Log::info('Receita falhou, tentando BrasilAPI', ['cnpj' => $cnpjLimpo]);
            $dados = $this->consultarBrasilApi($cnpjLimpo);
            if ($dados !== null) {
                Log::info('CNPJ encontrado na BrasilAPI', ['cnpj' => $cnpjLimpo]);
                return $dados;
            }

            // Tenta API 3: ReceitaWS (backup 2)
            Log::info('BrasilAPI falhou, tentando ReceitaWS', ['cnpj' => $cnpjLimpo]);
            $dados = $this->consultarReceitaWs($cnpjLimpo);
            if ($dados !== null) {
                Log::info('CNPJ encontrado na ReceitaWS', ['cnpj' => $cnpjLimpo]);
                return $dados;
            }

            // Nenhuma API retornou dados
            Log::warning('CNPJ não encontrado em nenhuma API', ['cnpj' => $cnpjLimpo]);
            return null;

        } catch (Exception $e) {
            Log::error('Erro ao consultar CNPJ', [
                'cnpj' => $cnpj,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Consulta na API Minha Receita
     */
    private function consultarMinhaReceita(string $cnpj): ?array
    {
        try {
            $url = self::MINHA_RECEITA_URL . '/' . $cnpj;
            
            Log::info('Consultando Minha Receita', [
                'url' => $url,
                'cnpj' => $cnpj
            ]);
            
            $response = $this->httpClient()
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'InfoVISA/3.0'
                ])
                ->get($url);

            Log::info('Resposta Minha Receita', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_size' => strlen($response->body())
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // A API retorna o CNPJ sem formatação
                if (isset($data['cnpj'])) {
                    Log::info('Dados encontrados na Minha Receita', [
                        'cnpj' => $data['cnpj'],
                        'razao_social' => $data['razao_social'] ?? 'N/A'
                    ]);
                    return $this->formatarMinhaReceita($data);
                }
            }

            Log::warning('Minha Receita não retornou dados válidos', [
                'status' => $response->status()
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Erro ao consultar Minha Receita', [
                'cnpj' => $cnpj,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Consulta na BrasilAPI
     */
    private function consultarBrasilApi(string $cnpj): ?array
    {
        try {
            $response = $this->httpClient()
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'InfoVISA/3.0'
                ])
                ->get(self::BRASIL_API_URL . '/' . $cnpj);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['cnpj'])) {
                    return $this->formatarBrasilApi($data);
                }
            }

            return null;

        } catch (Exception $e) {
            Log::warning('Erro ao consultar BrasilAPI', [
                'cnpj' => $cnpj,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Consulta na ReceitaWS
     */
    private function consultarReceitaWs(string $cnpj): ?array
    {
        try {
            $response = $this->httpClient()
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'InfoVISA/3.0'
                ])
                ->get(self::RECEITA_WS_URL . '/' . $cnpj);

            if ($response->successful()) {
                $data = $response->json();
                
                // ReceitaWS retorna status=ERROR quando não encontra
                if (isset($data['status']) && $data['status'] === 'ERROR') {
                    return null;
                }
                
                if (isset($data['cnpj'])) {
                    return $this->formatarReceitaWs($data);
                }
            }

            return null;

        } catch (Exception $e) {
            Log::warning('Erro ao consultar ReceitaWS', [
                'cnpj' => $cnpj,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Formata dados da API Minha Receita
     */
    private function formatarMinhaReceita(array $data): array
    {
        // Formata endereço completo
        $endereco = ($data['descricao_tipo_de_logradouro'] ?? '') . ' ' . ($data['logradouro'] ?? '');
        $endereco = trim($endereco);
        
        return [
            // Dados básicos
            'cnpj' => $data['cnpj'] ?? null,
            'razao_social' => $data['razao_social'] ?? null,
            'nome_fantasia' => $data['nome_fantasia'] ?? $data['razao_social'] ?? null,
            
            // Endereço
            'logradouro' => $endereco ?: ($data['logradouro'] ?? null),
            'endereco' => $endereco ?: ($data['logradouro'] ?? null),
            'numero' => $data['numero'] ?? 'S/N',
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cidade' => $data['municipio'] ?? null,
            'estado' => $data['uf'] ?? null,
            'cep' => $data['cep'] ?? null,
            'codigo_municipio_ibge' => $data['codigo_municipio_ibge'] ?? null,
            
            // Contato
            'telefone' => $this->formatarTelefone($data),
            'email' => $data['email'] ?? null,
            'ddd_telefone_1' => $data['ddd_telefone_1'] ?? '',
            'ddd_telefone_2' => $data['ddd_telefone_2'] ?? '',
            'ddd_fax' => $data['ddd_fax'] ?? '',
            
            // Dados empresariais
            'natureza_juridica' => $data['natureza_juridica'] ?? null,
            'tipo_setor' => $this->isPublico($data['natureza_juridica'] ?? ''),
            'porte' => $data['porte'] ?? null,
            'situacao_cadastral' => $data['situacao_cadastral'] ?? null,
            'descricao_situacao_cadastral' => $data['descricao_situacao_cadastral'] ?? null,
            'data_situacao_cadastral' => $this->formatarData($data['data_situacao_cadastral'] ?? null),
            'data_inicio_atividade' => $this->formatarData($data['data_inicio_atividade'] ?? null),
            
            // CNAE
            'cnae_fiscal' => $data['cnae_fiscal'] ?? null,
            'cnae_fiscal_descricao' => $data['cnae_fiscal_descricao'] ?? null,
            'cnaes_secundarios' => $data['cnaes_secundarios'] ?? [],
            'atividade_principal' => $data['cnae_fiscal_descricao'] ?? null,
            
            // Quadro Societário
            'qsa' => $data['qsa'] ?? [],
            
            // Outros dados
            'capital_social' => $data['capital_social'] ?? null,
            'opcao_pelo_mei' => $data['opcao_pelo_mei'] ?? false,
            'opcao_pelo_simples' => $data['opcao_pelo_simples'] ?? false,
            'data_opcao_pelo_simples' => $this->formatarData($data['data_opcao_pelo_simples'] ?? null),
            'data_exclusao_do_simples' => $this->formatarData($data['data_exclusao_do_simples'] ?? null),
            'regime_tributario' => $data['regime_tributario'] ?? [],
            'situacao_especial' => $data['situacao_especial'] ?? '',
            'motivo_situacao_cadastral' => $data['motivo_situacao_cadastral'] ?? '',
            'descricao_motivo_situacao_cadastral' => $data['descricao_motivo_situacao_cadastral'] ?? '',
            'identificador_matriz_filial' => $data['descricao_identificador_matriz_filial'] ?? '',
            'qualificacao_do_responsavel' => $data['qualificacao_do_responsavel'] ?? '',
            
            // Campos do sistema
            'tipo_pessoa' => 'juridica',
            'ativo' => ($data['situacao_cadastral'] ?? 0) == 2,
            'api_source' => 'minha_receita', // Identificador da fonte
        ];
    }

    /**
     * Formata dados da BrasilAPI
     */
    private function formatarBrasilApi(array $data): array
    {
        // BrasilAPI usa estrutura um pouco diferente
        $qsa = [];
        if (isset($data['qsa']) && is_array($data['qsa'])) {
            foreach ($data['qsa'] as $socio) {
                $qsa[] = [
                    'nome_socio' => $socio['nome_socio'] ?? '',
                    'qualificacao_socio' => $socio['qualificacao_socio'] ?? '',
                    'faixa_etaria' => $socio['faixa_etaria'] ?? '',
                ];
            }
        }

        $cnaes_secundarios = [];
        if (isset($data['cnaes_secundarios']) && is_array($data['cnaes_secundarios'])) {
            foreach ($data['cnaes_secundarios'] as $cnae) {
                $cnaes_secundarios[] = [
                    'codigo' => $cnae['codigo'] ?? '',
                    'descricao' => $cnae['descricao'] ?? '',
                ];
            }
        }

        return [
            'cnpj' => $data['cnpj'] ?? null,
            'razao_social' => $data['razao_social'] ?? null,
            'nome_fantasia' => $data['nome_fantasia'] ?? $data['razao_social'] ?? null,
            
            // Endereço
            'logradouro' => $data['descricao_tipo_de_logradouro'] . ' ' . $data['logradouro'] ?? null,
            'endereco' => $data['descricao_tipo_de_logradouro'] . ' ' . $data['logradouro'] ?? null,
            'numero' => $data['numero'] ?? 'S/N',
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cidade' => $data['municipio'] ?? null,
            'estado' => $data['uf'] ?? null,
            'cep' => $data['cep'] ?? null,
            'codigo_municipio_ibge' => $data['codigo_municipio_ibge'] ?? null,
            
            // Contato
            'telefone' => $this->formatarTelefoneBrasilApi($data),
            'email' => null, // BrasilAPI não retorna email
            'ddd_telefone_1' => $data['ddd_telefone_1'] ?? '',
            'ddd_telefone_2' => $data['ddd_telefone_2'] ?? '',
            'ddd_fax' => $data['ddd_fax'] ?? '',
            
            // Dados empresariais
            'natureza_juridica' => $data['natureza_juridica'] ?? null,
            'tipo_setor' => $this->isPublico($data['natureza_juridica'] ?? ''),
            'porte' => $data['porte'] ?? null,
            'situacao_cadastral' => $data['situacao_cadastral'] ?? null,
            'descricao_situacao_cadastral' => $data['descricao_situacao_cadastral'] ?? null,
            'data_situacao_cadastral' => $this->formatarData($data['data_situacao_cadastral'] ?? null),
            'data_inicio_atividade' => $this->formatarData($data['data_inicio_atividade'] ?? null),
            
            // CNAE
            'cnae_fiscal' => $data['cnae_fiscal'] ?? null,
            'cnae_fiscal_descricao' => $data['cnae_fiscal_descricao'] ?? null,
            'cnaes_secundarios' => $cnaes_secundarios,
            'atividade_principal' => $data['cnae_fiscal_descricao'] ?? null,
            
            // Quadro Societário
            'qsa' => $qsa,
            
            // Outros dados
            'capital_social' => $data['capital_social'] ?? null,
            'opcao_pelo_mei' => $data['opcao_pelo_mei'] ?? null,
            'opcao_pelo_simples' => $data['opcao_pelo_simples'] ?? null,
            'regime_tributario' => [],
            'situacao_especial' => $data['situacao_especial'] ?? '',
            'motivo_situacao_cadastral' => $data['motivo_situacao_cadastral'] ?? '',
            'descricao_motivo_situacao_cadastral' => $data['descricao_motivo_situacao_cadastral'] ?? '',
            'identificador_matriz_filial' => $data['descricao_identificador_matriz_filial'] ?? '',
            'qualificacao_do_responsavel' => '',
            
            'tipo_pessoa' => 'juridica',
            'ativo' => ($data['codigo_situacao_cadastral'] ?? 0) == 2,
            'api_source' => 'brasil_api', // Identificador da fonte
        ];
    }

    /**
     * Formata dados da ReceitaWS
     */
    private function formatarReceitaWs(array $data): array
    {
        // ReceitaWS usa estrutura diferente
        $qsa = [];
        if (isset($data['qsa']) && is_array($data['qsa'])) {
            foreach ($data['qsa'] as $socio) {
                $qsa[] = [
                    'nome_socio' => $socio['nome'] ?? '',
                    'qualificacao_socio' => $socio['qual'] ?? '',
                ];
            }
        }

        $cnaes_secundarios = [];
        if (isset($data['atividades_secundarias']) && is_array($data['atividades_secundarias'])) {
            foreach ($data['atividades_secundarias'] as $cnae) {
                $cnaes_secundarios[] = [
                    'codigo' => $cnae['code'] ?? '',
                    'descricao' => $cnae['text'] ?? '',
                ];
            }
        }

        return [
            'cnpj' => preg_replace('/[^0-9]/', '', $data['cnpj'] ?? ''),
            'razao_social' => $data['nome'] ?? null,
            'nome_fantasia' => $data['fantasia'] ?? $data['nome'] ?? null,
            
            // Endereço
            'logradouro' => $data['logradouro'] ?? null,
            'endereco' => $data['logradouro'] ?? null,
            'numero' => $data['numero'] ?? 'S/N',
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cidade' => $data['municipio'] ?? null,
            'estado' => $data['uf'] ?? null,
            'cep' => preg_replace('/[^0-9]/', '', $data['cep'] ?? ''),
            'codigo_municipio_ibge' => null,
            
            // Contato
            'telefone' => preg_replace('/[^0-9]/', '', $data['telefone'] ?? ''),
            'email' => $data['email'] ?? null,
            'ddd_telefone_1' => '',
            'ddd_telefone_2' => '',
            'ddd_fax' => '',
            
            // Dados empresariais
            'natureza_juridica' => $data['natureza_juridica'] ?? null,
            'tipo_setor' => $this->isPublico($data['natureza_juridica'] ?? ''),
            'porte' => $data['porte'] ?? null,
            'situacao_cadastral' => $data['situacao'] ?? null,
            'descricao_situacao_cadastral' => $data['situacao'] ?? null,
            'data_situacao_cadastral' => $this->formatarData($data['data_situacao'] ?? null),
            'data_inicio_atividade' => $this->formatarData($data['abertura'] ?? null),
            
            // CNAE
            'cnae_fiscal' => preg_replace('/[^0-9]/', '', $data['atividade_principal'][0]['code'] ?? ''),
            'cnae_fiscal_descricao' => $data['atividade_principal'][0]['text'] ?? null,
            'cnaes_secundarios' => $cnaes_secundarios,
            'atividade_principal' => $data['atividade_principal'][0]['text'] ?? null,
            
            // Quadro Societário
            'qsa' => $qsa,
            
            // Outros dados
            'capital_social' => $data['capital_social'] ?? null,
            'opcao_pelo_mei' => null,
            'opcao_pelo_simples' => null,
            'regime_tributario' => [],
            'situacao_especial' => $data['situacao_especial'] ?? '',
            'motivo_situacao_cadastral' => $data['motivo_situacao'] ?? '',
            'descricao_motivo_situacao_cadastral' => $data['motivo_situacao'] ?? '',
            'identificador_matriz_filial' => $data['tipo'] ?? '',
            'qualificacao_do_responsavel' => '',
            
            'tipo_pessoa' => 'juridica',
            'ativo' => strtoupper($data['situacao'] ?? '') === 'ATIVA',
            'api_source' => 'receita_ws', // Identificador da fonte
        ];
    }

    /**
     * Formata telefone dos dados da API
     */
    private function formatarTelefone(array $data): ?string
    {
        $ddd1 = $data['ddd_telefone_1'] ?? '';
        $tel1 = $data['telefone_1'] ?? '';
        
        if (!empty($ddd1) && !empty($tel1)) {
            return $ddd1 . $tel1;
        }
        
        $ddd2 = $data['ddd_telefone_2'] ?? '';
        $tel2 = $data['telefone_2'] ?? '';
        
        if (!empty($ddd2) && !empty($tel2)) {
            return $ddd2 . $tel2;
        }
        
        return null;
    }

    /**
     * Formata telefone da BrasilAPI
     */
    private function formatarTelefoneBrasilApi(array $data): ?string
    {
        $ddd1 = $data['ddd_telefone_1'] ?? '';
        
        if (!empty($ddd1)) {
            return $ddd1;
        }
        
        $ddd2 = $data['ddd_telefone_2'] ?? '';
        
        if (!empty($ddd2)) {
            return $ddd2;
        }
        
        return null;
    }

    /**
     * Determina se é estabelecimento público baseado na natureza jurídica
     */
    private function isPublico(string $naturezaJuridica): string
    {
        // Lista de palavras-chave que indicam natureza pública
        $palavrasChavePublicas = [
            'Órgão Público',
            'Autarquia',
            'Fundação Pública',
            'Empresa Pública',
            'Sociedade de Economia Mista',
            'Fundo Público',
            'Administração Direta',
            'Administração Indireta',
            'Poder Executivo',
            'Poder Legislativo',
            'Poder Judiciário',
            'Consórcio Público',
        ];

        // Verifica se a natureza jurídica contém alguma palavra-chave pública
        foreach ($palavrasChavePublicas as $palavra) {
            if (stripos($naturezaJuridica, $palavra) !== false) {
                return 'publico';
            }
        }

        return 'privado';
    }

    /**
     * Formata data para formato Y-m-d
     */
    private function formatarData(?string $data): ?string
    {
        if (empty($data)) {
            return null;
        }

        try {
            // Tenta vários formatos de data
            $formatos = ['Y-m-d', 'd/m/Y', 'Y/m/d'];
            
            foreach ($formatos as $formato) {
                $date = \DateTime::createFromFormat($formato, $data);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            }
            
            // Se nenhum formato funcionar, tenta strtotime
            return date('Y-m-d', strtotime($data));
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Formata CNPJ para exibição
     */
    public static function formatarCnpj(string $cnpj): string
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) === 14) {
            return substr($cnpj, 0, 2) . '.' . 
                   substr($cnpj, 2, 3) . '.' . 
                   substr($cnpj, 5, 3) . '/' . 
                   substr($cnpj, 8, 4) . '-' . 
                   substr($cnpj, 12, 2);
        }
        
        return $cnpj;
    }

    /**
     * Valida CNPJ
     */
    public static function validarCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        
        // Calcula os dígitos verificadores
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        
        $resto = $soma % 11;
        
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }
        
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        
        $resto = $soma % 11;
        
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
}
