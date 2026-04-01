<?php

namespace App\Services;

use App\Models\Estabelecimento;
use App\Models\Responsavel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ResponsavelTecnicoNomeGuard
{
    private const CONECTORES = [
        'A', 'AS', 'DA', 'DAS', 'DE', 'DO', 'DOS', 'E', 'O', 'OS',
    ];

    private const SUFIXOS_EMPRESARIAIS = [
        'EI', 'EIRELI', 'EPP', 'LIMITADA', 'LTDA', 'LTD', 'ME', 'MEI', 'SA', 'SLU', 'SS',
    ];

    public function obterMensagemDeBloqueio(
        string $nomeInformado,
        ?string $cpf = null,
        ?Estabelecimento $estabelecimentoTecnicoAdicional = null,
    ): ?string {
        $estabelecimentos = $this->buscarEstabelecimentosTecnicosPorCpf($cpf);

        if ($estabelecimentoTecnicoAdicional) {
            $estabelecimentos = $this->adicionarEstabelecimentoUnico($estabelecimentos, $estabelecimentoTecnicoAdicional);
        }

        if ($estabelecimentos->isEmpty()) {
            return null;
        }

        foreach ($estabelecimentos as $estabelecimento) {
            $comparacoes = [
                'Razão Social' => $estabelecimento->nome_razao_social,
                'Nome Fantasia' => $estabelecimento->nome_fantasia,
            ];

            foreach ($comparacoes as $campo => $nomeEstabelecimento) {
                if (!$this->saoMuitoParecidos($nomeInformado, $nomeEstabelecimento)) {
                    continue;
                }

                return "O nome do Responsável Técnico não pode ser igual ou muito parecido com a {$campo} do estabelecimento. Informe o nome da pessoa física responsável.";
            }
        }

        return null;
    }

    public function nomePareceNomeDoEstabelecimento(string $nomeInformado, Estabelecimento $estabelecimento): bool
    {
        return $this->saoMuitoParecidos($nomeInformado, $estabelecimento->nome_razao_social)
            || $this->saoMuitoParecidos($nomeInformado, $estabelecimento->nome_fantasia);
    }

    private function buscarEstabelecimentosTecnicosPorCpf(?string $cpf): Collection
    {
        $cpfLimpo = preg_replace('/\D/', '', (string) $cpf);

        if ($cpfLimpo === '') {
            return collect();
        }

        return Responsavel::where('cpf', $cpfLimpo)
            ->with([
                'estabelecimentos' => function ($query) {
                    $query->wherePivot('tipo_vinculo', 'tecnico')
                        ->wherePivot('ativo', true);
                },
            ])
            ->get()
            ->flatMap(fn (Responsavel $responsavel) => $responsavel->estabelecimentos)
            ->unique('id')
            ->values();
    }

    private function adicionarEstabelecimentoUnico(Collection $estabelecimentos, Estabelecimento $estabelecimento): Collection
    {
        if ($estabelecimento->exists && $estabelecimentos->contains('id', $estabelecimento->id)) {
            return $estabelecimentos;
        }

        return $estabelecimentos->push($estabelecimento);
    }

    private function saoMuitoParecidos(?string $nomeResponsavel, ?string $nomeEstabelecimento): bool
    {
        $nomePessoa = $this->normalizarNome($nomeResponsavel);
        $nomeEmpresa = $this->normalizarNome($nomeEstabelecimento, true);

        if ($nomePessoa === '' || $nomeEmpresa === '') {
            return false;
        }

        if ($nomePessoa === $nomeEmpresa) {
            return true;
        }

        if (min(strlen($nomePessoa), strlen($nomeEmpresa)) >= 12
            && (str_contains($nomePessoa, $nomeEmpresa) || str_contains($nomeEmpresa, $nomePessoa))) {
            return true;
        }

        similar_text($nomePessoa, $nomeEmpresa, $similaridade);
        if (min(strlen($nomePessoa), strlen($nomeEmpresa)) >= 12 && $similaridade >= 88.0) {
            return true;
        }

        $tokensPessoa = $this->tokensRelevantes($nomePessoa);
        $tokensEmpresa = $this->tokensRelevantes($nomeEmpresa);

        if (count($tokensPessoa) < 3 || count($tokensEmpresa) < 3) {
            return false;
        }

        $intersecao = count(array_intersect($tokensPessoa, $tokensEmpresa));
        $baseComparacao = min(count($tokensPessoa), count($tokensEmpresa));

        return $baseComparacao >= 3 && ($intersecao / $baseComparacao) >= 0.75;
    }

    private function normalizarNome(?string $texto, bool $removerSufixosEmpresariais = false): string
    {
        $texto = Str::upper(trim(Str::ascii((string) $texto)));
        $texto = preg_replace('/[^A-Z0-9\s]/', ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', (string) $texto);
        $texto = trim((string) $texto);

        if ($texto === '') {
            return '';
        }

        if (!$removerSufixosEmpresariais) {
            return $texto;
        }

        $tokens = array_values(array_filter(
            explode(' ', $texto),
            fn (string $token) => !in_array($token, self::SUFIXOS_EMPRESARIAIS, true)
        ));

        return trim(implode(' ', $tokens));
    }

    private function tokensRelevantes(string $texto): array
    {
        return array_values(array_unique(array_filter(
            explode(' ', $texto),
            fn (string $token) => strlen($token) > 2 && !in_array($token, self::CONECTORES, true)
        )));
    }
}