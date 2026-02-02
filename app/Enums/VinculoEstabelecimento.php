<?php

namespace App\Enums;

enum VinculoEstabelecimento: string
{
    case RESPONSAVEL_LEGAL = 'responsavel_legal';
    case RESPONSAVEL_TECNICO = 'responsavel_tecnico';
    case FUNCIONARIO = 'funcionario';
    case CONTADOR = 'contador';
    
    // Valores legados - mantidos para compatibilidade com dados existentes
    case PROPRIETARIO = 'proprietario';
    case GERENTE = 'gerente';
    case OUTRO = 'outro';

    /**
     * Retorna o label amigável do enum
     */
    public function label(): string
    {
        return match($this) {
            self::RESPONSAVEL_LEGAL => 'Responsável Legal',
            self::RESPONSAVEL_TECNICO => 'Responsável Técnico',
            self::FUNCIONARIO => 'Funcionário',
            self::CONTADOR => 'Contador',
            self::PROPRIETARIO => 'Proprietário',
            self::GERENTE => 'Gerente',
            self::OUTRO => 'Outro',
        };
    }

    /**
     * Retorna todos os vínculos como array [value => label]
     * Exclui valores legados para não aparecerem em novos cadastros
     */
    public static function toArray(): array
    {
        $ativos = [
            self::RESPONSAVEL_LEGAL,
            self::RESPONSAVEL_TECNICO,
            self::FUNCIONARIO,
            self::CONTADOR,
        ];
        
        return array_reduce(
            $ativos,
            fn($carry, $case) => $carry + [$case->value => $case->label()],
            []
        );
    }
    
    /**
     * Verifica se o vínculo é um valor legado (não deve ser usado em novos cadastros)
     */
    public function isLegado(): bool
    {
        return in_array($this, [self::PROPRIETARIO, self::GERENTE, self::OUTRO]);
    }
}


