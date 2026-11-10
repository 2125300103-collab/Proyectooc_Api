<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Condicion extends Model
{
    protected $table = 'condiciones';

    protected $fillable = ['nombre', 'imagen', 'estado'];

    protected function casts(): array
    {
        return ['estado' => 'boolean'];
    }
}
