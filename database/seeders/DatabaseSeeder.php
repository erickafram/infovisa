<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Dados básicos do sistema
            MunicipioSeeder::class,
            TipoDocumentoSeeder::class,
            TipoProcessoSeeder::class,
            TipoAcoesTableSeeder::class,
            
            // 2. Pactuação (regras de competência)
            PactuacaoSeeder::class,
            
            // 3. Usuários e estabelecimentos (dados de teste)
            UsuarioInternoSeeder::class,
            EstabelecimentoSeeder::class,
        ]);
    }
}
