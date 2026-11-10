<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    protected $table = 'detalles_pedidos';

    protected $fillable = ['pedido_id', 'coleccionable_id', 'cantidad', 'precio', 'descuento'];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'descuento' => 'decimal:2',
        ];
    }
}
