<?php

namespace Database\Seeders;

use App\Models\Estabelecimento;
use App\Models\UsuarioExterno;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstabelecimentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar usuários externos existentes
        $usuariosExternos = UsuarioExterno::take(3)->get();

        if ($usuariosExternos->isEmpty()) {
            $this->command->info('Nenhum usuário externo encontrado. Execute primeiro o UsuarioExternoSeeder.');
            return;
        }

        $estabelecimentos = [
            [
                'nome_fantasia' => 'Restaurante do João',
                'razao_social' => 'João Silva Restaurante ME',
                'cnpj' => '12345678000123',
                'endereco' => 'Rua das Flores',
                'numero' => '123',
                'complemento' => 'Sala 01',
                'bairro' => 'Centro',
                'cidade' => 'Palmas',
                'estado' => 'TO',
                'cep' => '77001000',
                'telefone' => '63999999999',
                'email' => 'contato@restaurantedojoao.com.br',
                'tipo_estabelecimento' => 'restaurante',
                'atividade_principal' => 'Restaurante com serviço de mesa e delivery',
                'usuario_externo_id' => $usuariosExternos->first()->id,
            ],
            [
                'nome_fantasia' => 'Farmácia São José',
                'razao_social' => 'Farmácia São José Ltda',
                'cnpj' => '98765432000198',
                'endereco' => 'Avenida Juscelino Kubitschek',
                'numero' => '456',
                'bairro' => 'Plano Diretor Norte',
                'cidade' => 'Palmas',
                'estado' => 'TO',
                'cep' => '77006450',
                'telefone' => '6333333333',
                'email' => 'contato@farmaciasaojose.com.br',
                'tipo_estabelecimento' => 'farmacia',
                'atividade_principal' => 'Comércio varejista de medicamentos e produtos farmacêuticos',
                'usuario_externo_id' => $usuariosExternos->first()->id,
            ],
            [
                'nome_fantasia' => 'Supermercado Econômico',
                'razao_social' => 'Supermercado Econômico S.A.',
                'cnpj' => '45678901000156',
                'endereco' => 'Quadra 104 Norte',
                'numero' => '10',
                'bairro' => 'Plano Diretor Norte',
                'cidade' => 'Palmas',
                'estado' => 'TO',
                'cep' => '77006420',
                'telefone' => '6332222222',
                'email' => 'vendas@supermercadoeconomico.com.br',
                'tipo_estabelecimento' => 'supermercado',
                'atividade_principal' => 'Comércio varejista de supermercados e hipermercados',
                'usuario_externo_id' => $usuariosExternos->first()->id,
            ],
            [
                'nome_fantasia' => 'Padaria Pão Quente',
                'razao_social' => 'Padaria Pão Quente ME',
                'cnpj' => '78901234000189',
                'endereco' => 'Rua 7 de Setembro',
                'numero' => '789',
                'bairro' => 'Centro',
                'cidade' => 'Palmas',
                'estado' => 'TO',
                'cep' => '77001200',
                'telefone' => '63988888888',
                'email' => 'contato@padaria-pao-quente.com.br',
                'tipo_estabelecimento' => 'padaria',
                'atividade_principal' => 'Fabricação e comércio de produtos de panificação',
                'usuario_externo_id' => $usuariosExternos->first()->id,
            ],
            [
                'nome_fantasia' => 'Clínica Odontológica Sorriso',
                'razao_social' => 'Clínica Odontológica Sorriso Ltda',
                'cnpj' => '32109876000145',
                'endereco' => 'Quadra 103 Sul',
                'numero' => '25',
                'complemento' => 'Sala 201',
                'bairro' => 'Plano Diretor Sul',
                'cidade' => 'Palmas',
                'estado' => 'TO',
                'cep' => '77015500',
                'telefone' => '6334444444',
                'email' => 'contato@clinicaodontologicasorriso.com.br',
                'tipo_estabelecimento' => 'clinica',
                'atividade_principal' => 'Serviços odontológicos especializados',
                'usuario_externo_id' => $usuariosExternos->first()->id,
            ],
        ];

        foreach ($estabelecimentos as $estabelecimento) {
            Estabelecimento::updateOrCreate(
                ['cnpj' => $estabelecimento['cnpj']],
                $estabelecimento
            );
        }

        $this->command->info('✅ ' . count($estabelecimentos) . ' estabelecimentos criados/atualizados com sucesso!');
    }
}
