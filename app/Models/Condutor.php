<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Condutor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'condutores';

    protected $fillable = [
        'nome',
        'cpf',
        'tipo',
        'validade_acesso',
        'ativo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    /**
     * Especialização de Professor
     *
     * @return HasOne<CondutorProfessor, $this>
     */
    public function professor(): HasOne
    {
        return $this->hasOne(CondutorProfessor::class, 'condutor_id');
    }

    /**
     * Especialização de Funcionário
     *
     * @return HasOne<CondutorFuncionario, $this>
     */
    public function funcionario(): HasOne
    {
        return $this->hasOne(CondutorFuncionario::class, 'condutor_id');
    }

    /**
     * Especialização de Prestador de Serviço
     *
     * @return HasOne<CondutorPrestador, $this>
     */
    public function prestador(): HasOne
    {
        return $this->hasOne(CondutorPrestador::class, 'condutor_id');
    }

    /**
     * Especialização de Visitante
     *
     * @return HasOne<CondutorVisitante, $this>
     */
    public function visitante(): HasOne
    {
        return $this->hasOne(CondutorVisitante::class, 'condutor_id');
    }

    /**
     * Veículos vinculados ao condutor (1:N)
     *
     * @return HasMany<Veiculo, $this>
     */
    public function veiculos(): HasMany
    {
        return $this->hasMany(Veiculo::class, 'condutor_id');
    }

    /**
     * Registros de passagens na cancela
     *
     * @return HasMany<RegistroAcesso, $this>
     */
    public function registrosAcesso(): HasMany
    {
        return $this->hasMany(RegistroAcesso::class, 'condutor_id');
    }

    /**
     * Retorna o código de acesso funcional do condutor de acordo com sua categoria.
     */
    public function codigoAcesso(): ?string
    {
        return match ($this->tipo) {
            'professor' => $this->professor?->codigo_acesso,
            'funcionario' => $this->funcionario?->codigo_acesso,
            'prestador' => $this->prestador?->codigo_acesso,
            default => null,
        };
    }

    /**
     * Busca um condutor ativo pelo código de acesso nas categorias permitidas.
     *
     * @param  array<int, string>  $tiposPermitidos
     */
    public static function buscarAtivoPorCodigo(
        string $codigo,
        array $tiposPermitidos = ['professor', 'funcionario', 'prestador']
    ): ?self {
        $codigoLimpo = strtoupper(trim($codigo));
        if ($codigoLimpo === '') {
            return null;
        }

        return self::query()
            ->with(['professor', 'funcionario', 'prestador', 'veiculos'])
            ->where('ativo', true)
            ->whereIn('tipo', $tiposPermitidos)
            ->where(function (Builder $q) use ($codigoLimpo): void {
                $q->whereHas('professor', fn (Builder $pq) => $pq->whereRaw('UPPER(codigo_acesso) = ?', [$codigoLimpo]))
                    ->orWhereHas('funcionario', fn (Builder $fq) => $fq->whereRaw('UPPER(codigo_acesso) = ?', [$codigoLimpo]))
                    ->orWhereHas('prestador', fn (Builder $prq) => $prq->whereRaw('UPPER(codigo_acesso) = ?', [$codigoLimpo]));
            })
            ->first();
    }
}
