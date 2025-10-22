<?php

namespace Database\Seeders;

use App\Models\TipoProcesso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoProcessoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'nome' => 'Licenciamento',
                'codigo' => 'licenciamento',
                'descricao' => 'Processo de licenciamento sanitário anual do estabelecimento',
                'anual' => true,
                'usuario_externo_pode_abrir' => true,
                'ativo' => true,
                'ordem' => 1,
            ],
            [
                'nome' => 'Análise de Rotulagem',
                'codigo' => 'analise_rotulagem',
                'descricao' => 'Análise e aprovação de rótulos de produtos',
                'anual' => false,
                'usuario_externo_pode_abrir' => true,
                'ativo' => true,
                'ordem' => 2,
            ],
            [
                'nome' => 'Projeto Arquitetônico',
                'codigo' => 'projeto_arquitetonico',
                'descricao' => 'Análise de projeto arquitetônico para adequação sanitária',
                'anual' => false,
                'usuario_externo_pode_abrir' => true,
                'ativo' => true,
                'ordem' => 3,
            ],
            [
                'nome' => 'Administrativo',
                'codigo' => 'administrativo',
                'descricao' => 'Processos administrativos diversos',
                'anual' => false,
                'usuario_externo_pode_abrir' => false,
                'ativo' => true,
                'ordem' => 4,
            ],
            [
                'nome' => 'Descentralização',
                'codigo' => 'descentralizacao',
                'descricao' => 'Processos de descentralização de ações de vigilância sanitária',
                'anual' => false,
                'usuario_externo_pode_abrir' => false,
                'ativo' => true,
                'ordem' => 5,
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoProcesso::create($tipo);
        }
    }
}
