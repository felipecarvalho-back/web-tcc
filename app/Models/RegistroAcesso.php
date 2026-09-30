<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistroAcesso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'registros_acesso';

    protected $fillable = [
        'veiculo_id',
        'condutor_id',
        'placa_registro',
        'cancela',
        'data_hora_entrada',
        'operador_id',
        'foto_entrada_path',
        'placa_corrigida',
        'data_hora_saida',
        'status',
        'justificativa',
        'tipo_ocupante',
        'passagem_origem_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_hora_entrada' => 'datetime',
            'data_hora_saida' => 'datetime',
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
     * @return BelongsTo<Usuario, $this>
     */
    public function operador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'operador_id');
    }

    /**
     * Passagem principal do condutor (caso este registro seja de um carona/passageiro)
     *
     * @return BelongsTo<RegistroAcesso, $this>
     */
    public function passagemOrigem(): BelongsTo
    {
        return $this->belongsTo(RegistroAcesso::class, 'passagem_origem_id');
    }

    /**
     * Caronas/Passageiros que entraram junto nesta mesma passagem do veículo
     *
     * @return HasMany<RegistroAcesso, $this>
     */
    public function caronas(): HasMany
    {
        return $this->hasMany(RegistroAcesso::class, 'passagem_origem_id');
    }
}
