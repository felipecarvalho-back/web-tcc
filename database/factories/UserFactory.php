<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usuario>
 */
class UserFactory extends Factory
{
    protected $model = Usuario::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'cpf' => fake()->numerify('###.###.###-##'),
            'email' => fake()->unique()->safeEmail(),
            'senha' => static::$password ??= Hash::make('password'),
            'perfil' => 'operador',
            'codigo_operador' => 'OP-'.fake()->unique()->numberBetween(100, 999),
            'ativo' => true,
            'remember_token' => Str::random(10),
        ];
    }
}
