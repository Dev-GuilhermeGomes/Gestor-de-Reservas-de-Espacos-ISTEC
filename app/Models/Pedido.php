<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'utilizador_id',
        'espaco_id',
        'tipo_utilizacao_id',
        'inicio',
        'fim',
        'participantes',
        'motivo',
        'estado',
        'decidido_por',
        'decidido_em',
        'justificacao_decisao',
    ];

    protected function casts(): array
    {
        return [
            'inicio' => 'datetime',
            'fim' => 'datetime',
            'decidido_em' => 'datetime',
        ];
    }

    public function espaco(): BelongsTo
    {
        return $this->belongsTo(Espaco::class);
    }

    public function utilizador(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
