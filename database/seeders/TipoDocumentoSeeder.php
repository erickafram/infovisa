<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDocumento;

class TipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'nome' => 'Alvará Sanitário',
                'codigo' => 'alvara_sanitario',
                'descricao' => 'Documento de autorização sanitária',
                'ativo' => true,
                'ordem' => 1,
            ],
            [
                'nome' => 'Memorando',
                'codigo' => 'memorando',
                'descricao' => 'Comunicação interna oficial',
                'ativo' => true,
                'ordem' => 2,
            ],
            [
                'nome' => 'Notificação',
                'codigo' => 'notificacao',
                'descricao' => 'Notificação oficial',
                'ativo' => true,
                'ordem' => 3,
            ],
            [
                'nome' => 'Ofício',
                'codigo' => 'oficio',
                'descricao' => 'Comunicação externa oficial',
                'ativo' => true,
                'ordem' => 4,
            ],
            [
                'nome' => 'Termo',
                'codigo' => 'termo',
                'descricao' => 'Termo de compromisso ou responsabilidade',
                'ativo' => true,
                'ordem' => 5,
            ],
            [
                'nome' => 'Relatório',
                'codigo' => 'relatorio',
                'descricao' => 'Relatório técnico ou de inspeção',
                'ativo' => true,
                'ordem' => 6,
            ],
            [
                'nome' => 'Declaração',
                'codigo' => 'declaracao',
                'descricao' => 'Declaração oficial',
                'ativo' => true,
                'ordem' => 7,
            ],
            [
                'nome' => 'Atestado',
                'codigo' => 'atestado',
                'descricao' => 'Atestado ou certificado',
                'ativo' => true,
                'ordem' => 8,
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoDocumento::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }
        
        $this->command->info('Tipos de documentos cadastrados com sucesso!');
    }
}
