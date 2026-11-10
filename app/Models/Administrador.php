<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Administrador extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'administradores';
    protected $fillable = ['nombres', 'apellidos', 'correo', 'usuario', 'contraseña', 'imagen', 'rol', 'estado'];
    protected $hidden = ['contraseña'];

    protected function casts(): array
    {
        return ['estado' => 'boolean'];
    }

    public function getAuthPassword(): string
    {
        return $this->contraseña;
    }
}
