<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Espaco extends Model
{
    protected $table = 'espacos';

    protected $fillable = [
        'edificio',
        'nome',
        'tipo',
        'capacidade',
        'piso',
        'descricao',
        'ativo',
    ];

    public function gestores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'espaco_gestores', 'espaco_id', 'utilizador_id');
    }

    public function equipamentos(): HasMany
    {
        return $this->hasMany(Equipamento::class, 'espaco_id');
    }

    public function getNomeCompletoAttribute(): string
    {
        return $this->edificio.' - '.$this->nome;
    }
}
