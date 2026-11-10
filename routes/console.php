<?php

use App\Models\Administrador;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('api:admin', function () {
    $data = Validator::make([
        'nombres' => $this->ask('Nombre(s)'),
        'apellidos' => $this->ask('Apellidos'),
        'correo' => $this->ask('Correo'),
        'usuario' => $this->ask('Usuario'),
        'contraseña' => $this->secret('Contraseña'),
        'contraseña_confirmation' => $this->secret('Confirma la contraseña'),
        'rol' => $this->ask('Rol', 'Administrador'),
    ], [
        'nombres' => ['required', 'string', 'max:255'],
        'apellidos' => ['required', 'string', 'max:255'],
        'correo' => ['required', 'email', 'max:255', 'unique:administradores,correo'],
        'usuario' => ['required', 'string', 'max:50', 'unique:administradores,usuario'],
        'contraseña' => ['required', 'string', 'min:8', 'confirmed'],
        'rol' => ['required', 'string', 'max:255'],
    ])->validate();

    $data['contraseña'] = Hash::make($data['contraseña']);
    $data['estado'] = true;
    unset($data['contraseña_confirmation']);

    Administrador::query()->create($data);

    $this->info('Administrador activo creado correctamente. Ya puedes iniciar sesión en la API.');
})->purpose('Create an active administrator for API authentication');
