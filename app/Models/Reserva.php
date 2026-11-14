<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    public function utilizador()
    {
        return $this->belongsTo(User::class, 'utilizador_id');
    }

    public function espaco()
    {
        return $this->belongsTo(Espaco::class, 'espaco_id');
    }

    protected function casts(): array
    {   
        return [
            'inicio' => 'datetime',
            'fim' => 'datetime',
        ];
    }
}
