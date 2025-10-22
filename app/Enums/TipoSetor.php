<?php

namespace App\Enums;

enum TipoSetor: string
{
    case Publico = 'publico';
    case Privado = 'privado';

    public function label(): string
    {
        return match ($this) {
            self::Publico => 'Público',
            self::Privado => 'Privado',
        };
    }

    public static function toArray(): array
    {
        return array_reduce(self::cases(), function ($carry, $item) {
            $carry[$item->value] = $item->label();
            return $carry;
        }, []);
    }
}



