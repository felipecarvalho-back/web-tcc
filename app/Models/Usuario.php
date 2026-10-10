<?php

namespace App\Models;

use Database\Factories\UserFactory;
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

    protected $appends = ['data_criacao'];

    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'senha',
        'perfil',
        'codigo_operador',
        'ativo',
    ];

    protected function dataCriacao(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->created_at?->format('d/m/Y'),
        );
    }

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

    public function getAuthPasswordName(): string
    {
        return 'senha';
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
     * Gera o próximo código sequencial de operador com base no perfil (ex: GDA-105, ADM-002, SUP-201).
     */
    public static function gerarProximoCodigo(string $perfil = 'operador'): string
    {
        $prefixo = match ($perfil) {
            'admin' => 'ADM',
            'supervisor' => 'SUP',
            default => 'GDA',
        };

        // Pega todos os códigos com o prefixo para encontrar confiavelmente o maior número
        $codigos = self::withTrashed()
            ->where('codigo_operador', 'like', "{$prefixo}-%")
            ->pluck('codigo_operador');

        $maiorNumero = match ($prefixo) {
            'ADM' => 0,
            'SUP' => 200,
            default => 100, // Inicia a partir de 100 para GDA (ex: GDA-101, GDA-102...)
        };

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
