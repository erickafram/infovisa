<?php

namespace App\Enums;

enum VinculoEstabelecimento: string
{
    case PROPRIETARIO = 'proprietario';
    case RESPONSAVEL_TECNICO = 'responsavel_tecnico';
    case RESPONSAVEL_LEGAL = 'responsavel_legal';
    case GERENTE = 'gerente';
    case OUTRO = 'outro';

    /**
     * Retorna o label amigável do enum
     */
    public function label(): string
    {
        return match($this) {
            self::PROPRIETARIO => 'Proprietário',
            self::RESPONSAVEL_TECNICO => 'Responsável Técnico',
            self::RESPONSAVEL_LEGAL => 'Responsável Legal',
            self::GERENTE => 'Gerente',
            self::OUTRO => 'Outro',
        };
    }

    /**
     * Retorna todos os vínculos como array [value => label]
     */
    public static function toArray(): array
    {
        return array_reduce(
            self::cases(),
            fn($carry, $case) => $carry + [$case->value => $case->label()],
            []
        );
    }
}


