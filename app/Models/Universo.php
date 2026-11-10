<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Universo extends Model
{
    protected $fillable = ['nombre', 'imagen', 'estado'];

    protected function casts(): array
    {
        return ['estado' => 'boolean'];
    }
}
