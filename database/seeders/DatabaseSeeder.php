<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Usuario::firstOrCreate(
            ['email' => 'operador@fatec.sp.gov.br'],
            [
                'nome' => 'Operador Sentinela',
                'cpf' => '111.222.333-44',
                'senha' => '123456',
                'perfil' => 'operador',
                'codigo_operador' => 'GDA-104',
                'ativo' => true,
            ]
        );

        Usuario::firstOrCreate(
            ['email' => 'admin@fatec.sp.gov.br'],
            [
                'nome' => 'Administrador FATEC',
                'cpf' => '000.000.000-00',
                'senha' => '123456',
                'perfil' => 'admin',
                'codigo_operador' => 'ADM-001',
                'ativo' => true,
            ]
        );
    }
}
