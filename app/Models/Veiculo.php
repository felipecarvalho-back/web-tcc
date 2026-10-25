<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veiculo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'veiculos';

    protected $fillable = [
        'condutor_id',
        'placa',
        'modelo',
        'cor',
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
     * Condutor proprietário do veículo
     *
     * @return BelongsTo<Condutor, $this>
     */
    public function condutor(): BelongsTo
    {
        return $this->belongsTo(Condutor::class, 'condutor_id');
    }

    /**
     * Histórico de passagens na cancela
     *
     * @return HasMany<RegistroAcesso, $this>
     */
    public function registrosAcesso(): HasMany
    {
        return $this->hasMany(RegistroAcesso::class, 'veiculo_id');
    }

    /**
     * Busca um veículo ativo pela placa (com ou sem hífen) cujo condutor esteja ativo.
     *
     * @param  array<int, string>  $tiposCondutor
     */
    public static function buscarAtivoPorPlaca(
        ?string $placa,
        array $tiposCondutor = ['professor', 'funcionario', 'prestador']
    ): ?self {
        $placaFormatada = RegistroAcesso::formatarPlaca($placa);
        if ($placaFormatada === '' || $placaFormatada === RegistroAcesso::PLACA_NAO_IDENTIFICADA) {
            return null;
        }

        $semHifen = str_replace('-', '', $placaFormatada);

        return self::query()
            ->with(['condutor.professor', 'condutor.funcionario', 'condutor.prestador'])
            ->where('ativo', true)
            ->whereIn('placa', [$placaFormatada, $semHifen])
            ->whereHas('condutor', function ($q) use ($tiposCondutor): void {
                $q->where('ativo', true)->whereIn('tipo', $tiposCondutor);
            })
            ->first();
    }
}
