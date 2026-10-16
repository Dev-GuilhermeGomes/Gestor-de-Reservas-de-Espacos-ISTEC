<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'utilizadores';

    protected $fillable = ['nome', 'email', 'password', 'perfil', 'ativo'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'ativo' => 'boolean'];
    }

    // a tabela não tem remember_token
    public function getRememberTokenName()
    {
        return '';
    }
}