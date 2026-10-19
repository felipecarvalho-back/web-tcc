<?php

namespace App\Models;

use App\Enums\PerfilUsuario;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function dataCriacao(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->created_at?->format('d/m/Y'),
        );
    }

    public function getAuthPasswordName(): string
    {
        return 'senha';
    }

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    /**
     * Verifica se o usuário possui perfil de administrador.
     */
    public function isAdmin(): bool
    {
        return $this->perfil === PerfilUsuario::Admin->value;
    }

    /**
     * Verifica se o usuário possui permissão para operar a portaria/guarita.
     */
    public function canAccessPortaria(): bool
    {
        return in_array($this->perfil, PerfilUsuario::portariaValues(), true);
    }

    /**
     * Iniciais do nome do usuário para avatar.
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
     * Filtra a listagem de usuários por termo de busca (case-insensitive no PostgreSQL e MySQL), perfil e status.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFiltrar(Builder $query, string $busca = '', string $perfil = 'todos', string $status = 'todos'): Builder
    {
        $termo = trim($busca);

        return $query
            ->when($termo !== '', function (Builder $q) use ($termo) {
                $q->where(function (Builder $sub) use ($termo) {
                    $sub->whereLike('nome', "%{$termo}%")
                        ->orWhereLike('email', "%{$termo}%")
                        ->orWhereLike('cpf', "%{$termo}%")
                        ->orWhereLike('codigo_operador', "%{$termo}%");
                });
            })
            ->when($perfil !== 'todos', fn (Builder $q) => $q->where('perfil', $perfil))
            ->when($status === 'ativo', fn (Builder $q) => $q->where('ativo', true))
            ->when($status === 'inativo', fn (Builder $q) => $q->where('ativo', false));
    }

    /**
     * Gera o próximo código sequencial de operador com base no perfil (ex: GDA-105, ADM-002, SUP-201).
     */
    public static function gerarProximoCodigo(string $perfil = 'operador'): string
    {
        $perfilEnum = PerfilUsuario::tryFrom($perfil) ?? PerfilUsuario::Operador;
        $prefixo = $perfilEnum->prefixoCodigo();
        $maiorNumero = $perfilEnum->numeroInicial();

        $codigos = self::withTrashed()
            ->where('codigo_operador', 'like', "{$prefixo}-%")
            ->pluck('codigo_operador');

        foreach ($codigos as $cod) {
            if (preg_match('/-(\d+)$/', (string) $cod, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maiorNumero) {
                    $maiorNumero = $num;
                }
            }
        }

        return sprintf('%s-%03d', $prefixo, $maiorNumero + 1);
    }

    /**
     * @return HasMany<RegistroAcesso, $this>
     */
    public function registrosAcesso(): HasMany
    {
        return $this->hasMany(RegistroAcesso::class, 'operador_id');
    }
}
