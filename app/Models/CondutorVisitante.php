<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CondutorVisitante extends Model
{
    use HasFactory;

    protected $table = 'condutores_visitantes';

    protected $primaryKey = 'condutor_id';

    public $incrementing = false;

    protected $fillable = [
        'condutor_id',
        'motivo_visita',
        'setor_destino',
        'permanencia_estimada',
    ];

    /**
     * @return BelongsTo<Condutor, $this>
     */
    public function condutor(): BelongsTo
    {
        return $this->belongsTo(Condutor::class, 'condutor_id');
    }
}
