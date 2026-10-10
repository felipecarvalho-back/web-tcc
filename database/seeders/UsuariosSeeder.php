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
    Usuario::query()->create([
        'nome' => 'Administrador',
        'cpf' => '999.999.999-99',
        'email' => 'admin@teste.com',
        'senha' => 'admin123',
        'perfil' => 'admin',
        'codigo_operador' => 'ADM-001',
        'ativo' => true,
    ]);

    Usuario::query()->create([
        'nome' => 'Matheus',
        'cpf' => '111.222.333-44',
        'email' => 'teste@teste.com',
        'senha' => '123456',
        'perfil' => 'operador',
        'codigo_operador' => 'GDA-100',
        'ativo' => true,
    ]);
}
}