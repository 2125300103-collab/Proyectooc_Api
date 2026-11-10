<?php

namespace Database\Seeders;

use App\Models\Administrador;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        Administrador::updateOrCreate(
            ['usuario' => 'admin'],
            [
                'nombres' => 'Administrador',
                'apellidos' => 'Principal',
                'correo' => 'admin@collectorstore.com',
                'contraseña' => Hash::make('admin123'),
                'rol' => 'Dueño',
                'estado' => 1,
            ]
        );

        Administrador::updateOrCreate(
            ['usuario' => 'ana'],
            [
                'nombres' => 'Ana',
                'apellidos' => 'García',
                'correo' => 'ana@collectorstore.com',
                'contraseña' => Hash::make('secreto123'),
                'rol' => 'Inventario',
                'estado' => 1,
            ]
        );
    }
}
