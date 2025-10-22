<?php

namespace Database\Seeders;

use App\Enums\NivelAcesso;
use App\Models\UsuarioInterno;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioInternoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar usuário administrador padrão
        UsuarioInterno::create([
            'nome' => 'Administrador do Sistema',
            'cpf' => '00000000000',
            'email' => 'admin@infovisa.gov.br',
            'telefone' => '63999999999',
            'matricula' => 'ADM001',
            'cargo' => 'Administrador',
            'nivel_acesso' => NivelAcesso::Administrador,
            'municipio' => null,
            'password' => Hash::make('Admin@123'),
            'ativo' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Usuário administrador criado com sucesso!');
        $this->command->info('📧 Email: admin@infovisa.gov.br');
        $this->command->info('🔑 Senha: Admin@123');
    }
}
