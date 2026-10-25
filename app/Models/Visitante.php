<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitante extends Model
{
    use HasFactory;

    protected $table = 'visitantes';

    protected $fillable = [
        'nome',
        'cpf',
        'rg',
        'telefone',
        'placa_veiculo',
        'modelo_veiculo',
        'destino_motivo',
        'status',
        'data_saida',
    ];

    protected $casts = [
        'data_saida' => 'datetime',
    ];
}
