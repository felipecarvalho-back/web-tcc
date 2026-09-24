<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistroAcesso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'registros_acesso';

    protected $fillable = [
        'veiculo_id',
        'condutor_id',
        'placa_registro',
        'data_hora_entrada',
        'operador_id',
        'taxa_confianca',
        'foto_entrada_path',
        'placa_corrigida',
        'data_hora_saida',
        'status',
        'justificativa',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_hora_entrada' => 'datetime',
            'data_hora_saida' => 'datetime',
            'taxa_confianca' => 'integer',
        ];
    }

    /**
     * Veículo da passagem (se cadastrado)
     *
     * @return BelongsTo<Veiculo, $this>
     */
    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    /**
     * Condutor associado à passagem
     *
     * @return BelongsTo<Condutor, $this>
     */
    public function condutor(): BelongsTo
    {
        return $this->belongsTo(Condutor::class, 'condutor_id');
    }

    /**
     * Operador/Porteiro que registrou a passagem
     *
     * @return BelongsTo<User, $this>
     */
    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operador_id');
    }
}
