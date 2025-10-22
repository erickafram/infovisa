<?php

namespace App\Enums;

enum NivelAcesso: string
{
    case Administrador = 'administrador';
    case GestorEstadual = 'gestor_estadual';
    case GestorMunicipal = 'gestor_municipal';
    case TecnicoEstadual = 'tecnico_estadual';
    case TecnicoMunicipal = 'tecnico_municipal';

    /**
     * Retorna o label legível do nível de acesso
     */
    public function label(): string
    {
        return match($this) {
            self::Administrador => 'Administrador',
            self::GestorEstadual => 'Gestor Estadual',
            self::GestorMunicipal => 'Gestor Municipal',
            self::TecnicoEstadual => 'Técnico Estadual',
            self::TecnicoMunicipal => 'Técnico Municipal',
        };
    }

    /**
     * Retorna a descrição do nível de acesso
     */
    public function descricao(): string
    {
        return match($this) {
            self::Administrador => 'Acesso completo ao sistema, incluindo gestão de usuários',
            self::GestorEstadual => 'Gestão de processos e estabelecimentos em nível estadual',
            self::GestorMunicipal => 'Gestão de processos e estabelecimentos em nível municipal',
            self::TecnicoEstadual => 'Análise técnica de processos em nível estadual',
            self::TecnicoMunicipal => 'Análise técnica de processos em nível municipal',
        };
    }

    /**
     * Verifica se o nível é de administrador
     */
    public function isAdmin(): bool
    {
        return $this === self::Administrador;
    }

    /**
     * Verifica se o nível é de gestor
     */
    public function isGestor(): bool
    {
        return in_array($this, [self::GestorEstadual, self::GestorMunicipal]);
    }

    /**
     * Verifica se o nível é estadual
     */
    public function isEstadual(): bool
    {
        return in_array($this, [self::GestorEstadual, self::TecnicoEstadual]);
    }

    /**
     * Verifica se o nível é municipal
     */
    public function isMunicipal(): bool
    {
        return in_array($this, [self::GestorMunicipal, self::TecnicoMunicipal]);
    }

    /**
     * Retorna todos os níveis de acesso
     */
    public static function all(): array
    {
        return [
            self::Administrador,
            self::GestorEstadual,
            self::GestorMunicipal,
            self::TecnicoEstadual,
            self::TecnicoMunicipal,
        ];
    }

    /**
     * Retorna todos os níveis de acesso como array [value => label]
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn($case) => $case->value, self::cases()),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }
}

