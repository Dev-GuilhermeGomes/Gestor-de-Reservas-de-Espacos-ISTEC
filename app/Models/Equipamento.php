<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipamento extends Model
{
    protected $table = 'equipamentos';

    public $timestamps = false;

    protected $fillable = [
        'espaco_id',
        'nome',
        'descricao',
        'reservavel',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'reservavel' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    public function espaco(): BelongsTo
    {
        return $this->belongsTo(Espaco::class);
    }
}
