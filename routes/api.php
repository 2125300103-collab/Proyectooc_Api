<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiResourceController;
use App\Http\Controllers\LoginController;

Route::post('/login', [LoginController::class, 'login'])->name('api.login');

Route::middleware(['auth:sanctum', 'admin.active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('api.logout');

    $resources = [
        'administradores',
        'categorias',
        'clientes',
        'coleccionables',
        'condiciones',
        'detalles_pedidos',
        'pedidos',
        'productos',
        'proveedores',
        'universos',
        'ventas',
    ];

    Route::get('/{resource}', [ApiResourceController::class, 'index'])
        ->whereIn('resource', $resources);
    Route::post('/{resource}', [ApiResourceController::class, 'store'])
        ->whereIn('resource', $resources);
    Route::get('/{resource}/{id}', [ApiResourceController::class, 'show'])
        ->whereIn('resource', $resources)
        ->whereNumber('id');
    Route::put('/{resource}/{id}', [ApiResourceController::class, 'update'])
        ->whereIn('resource', $resources)
        ->whereNumber('id');
    Route::patch('/{resource}/{id}', [ApiResourceController::class, 'update'])
        ->whereIn('resource', $resources)
        ->whereNumber('id');
    Route::delete('/{resource}/{id}', [ApiResourceController::class, 'destroy'])
        ->whereIn('resource', $resources)
        ->whereNumber('id');
});
