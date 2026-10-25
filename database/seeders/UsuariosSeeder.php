<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Usuario::query()->firstOrCreate(
            ['codigo_operador' => 'ADM-001'],
            [
                'nome' => 'Carlos Eduardo Silva',
                'cpf' => '999.999.999-99',
                'email' => 'admin@teste.com',
                'senha' => 'admin123',
                'perfil' => 'admin',
                'ativo' => true,
            ]
        );

        Usuario::query()->firstOrCreate(
            ['codigo_operador' => 'GDA-104'],
            [
                'nome' => 'Guarda Silva',
                'cpf' => '123.456.789-01',
                'email' => 'guarda.silva@fatec.sp.gov.br',
                'senha' => 'guarda123',
                'perfil' => 'operador',
                'ativo' => true,
            ]
        );

        Usuario::query()->firstOrCreate(
            ['codigo_operador' => 'GDA-105'],
            [
                'nome' => 'Guarda Ribeiro',
                'cpf' => '123.456.789-02',
                'email' => 'guarda.ribeiro@fatec.sp.gov.br',
                'senha' => 'guarda123',
                'perfil' => 'operador',
                'ativo' => true,
            ]
        );
    }
}
