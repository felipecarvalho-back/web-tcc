<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'cpf' => fake()->unique()->numerify('###########'),
            'email' => fake()->unique()->safeEmail(),
            'senha' => 'password',
            'perfil' => 'operador',
            'codigo_operador' => 'GDA-' . fake()->unique()->numerify('###'),
            'ativo' => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'perfil' => 'admin',
            'codigo_operador' => 'ADM-' . fake()->unique()->numerify('###'),
        ]);
    }
}