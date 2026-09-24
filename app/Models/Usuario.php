<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Usuario extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'senha',
        'perfil',
        'codigo_operador',
        'ativo',
    ];

    protected $hidden = [
        'senha',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'senha' => 'hashed',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    /**
     * Iniciais do nome do usuário para avatar
     */
    public function initials(): string
    {
        $displayName = $this->nome ?? 'Usuario';
        $initials = Str::initials($displayName, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * @return HasMany<RegistroAcesso, $this>
     */
    public function registrosAcesso(): HasMany
    {
        return $this->hasMany(RegistroAcesso::class, 'operador_id');
    }
}
