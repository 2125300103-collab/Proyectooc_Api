<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = ['cliente_id', 'fecha', 'iva', 'descuento', 'total', 'estado'];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'iva' => 'decimal:2',
            'descuento' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }
}
