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
                'nome' => 'Administrador',
                'cpf' => '999.999.999-99',
                'email' => 'admin@teste.com',
                'senha' => 'admin123',
                'perfil' => 'admin',
                'ativo' => true,
            ]
        );
    }
}
