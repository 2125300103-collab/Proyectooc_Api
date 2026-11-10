<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coleccionable extends Model
{
    protected $fillable = [
        'nombre', 'descripcion', 'categoria_id', 'universo_id', 'condicion_id',
        'precio', 'existencia', 'descuento', 'imagen1', 'imagen2', 'imagen3', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'descuento' => 'decimal:2',
            'estado' => 'boolean',
        ];
    }
}
