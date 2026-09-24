<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CondutorPrestador extends Model
{
    use HasFactory;

    protected $table = 'condutores_prestadores';

    protected $primaryKey = 'condutor_id';

    public $incrementing = false;

    protected $fillable = [
        'condutor_id',
        'empresa',
        'inicio_contrato',
        'fim_contrato',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'inicio_contrato' => 'date',
            'fim_contrato' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Condutor, $this>
     */
    public function condutor(): BelongsTo
    {
        return $this->belongsTo(Condutor::class, 'condutor_id');
    }
}
