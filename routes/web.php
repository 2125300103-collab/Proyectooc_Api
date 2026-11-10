<?php

//use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\AdministradorController;
//use App\Http\Controllers\CategoriaController;
//use App\Http\Controllers\ClienteController;
//use App\Http\Controllers\GeolocalizacionController;
//use App\Http\Controllers\AdminController;
//use App\Http\Controllers\LoginController;
//use App\Http\Controllers\ProductoController;
//use App\Http\Controllers\ProveedorController;

// Route::get('/', [ProductoController::class, 'listar'])->name('inicio');
// Route::get('/inicio', [ProductoController::class, 'listar'])->name('catalogo.inicio');
// Route::get('/geolocalizacion/obtener', [GeolocalizacionController::class, 'obtenerUbicacion'])
// 	->name('geolocalizacion.obtener');

// Route::get('/login', [LoginController::class, 'show'])->name('login');
// Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
// Route::get('/auth/google', [LoginController::class, 'redirectToGoogle'])
// 	->name('auth.google.redirect');
// Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback'])
// 	->name('auth.google.callback');
// Route::post('/logout', [LoginController::class, 'logout'])
//     ->middleware('auth:admin,web')
//     ->name('logout');

// Route::middleware('auth:admin')->group(function () {
//     Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

//     Route::get('/administradores/listar', [AdministradorController::class, 'listar'])->name('administradores.listar');
//     Route::get('/administradores/crear', [AdministradorController::class, 'vistaFormulario'])->name('administradores.formulario');
//     Route::post('/administradores/guardar', [AdministradorController::class, 'registrar'])->name('administradores.registrar');
//     Route::get('/administradores/editar/{id}', [AdministradorController::class, 'vistaEdicion'])->name('administradores.edicion');
//     Route::put('/administradores/actualizar/{id}', [AdministradorController::class, 'actualizar'])->name('administradores.actualizar');
//     Route::get('/administradores/mostrar/{id}', [AdministradorController::class, 'vistaMostrar'])->name('administradores.mostrar');
//     Route::delete('/administradores/borrar/{id}', [AdministradorController::class, 'borrar'])->name('administradores.borrar');

//     Route::get('/categorias/listar', [CategoriaController::class, 'listar'])->name('categorias.listar');
//     Route::get('/categorias/crear', [CategoriaController::class, 'vistaFormulario'])->name('categorias.formulario');
//     Route::post('/categorias/guardar', [CategoriaController::class, 'registrar'])->name('categorias.registrar');
//     Route::get('/categorias/editar/{id}', [CategoriaController::class, 'vistaEdicion'])->name('categorias.edicion');
//     Route::put('/categorias/actualizar/{id}', [CategoriaController::class, 'actualizar'])->name('categorias.actualizar');
//     Route::get('/categorias/mostrar/{id}', [CategoriaController::class, 'vistaMostrar'])->name('categorias.mostrar');
//     Route::delete('/categorias/borrar/{id}', [CategoriaController::class, 'borrar'])->name('categorias.borrar');

//     Route::get('/clientes/listar', [ClienteController::class, 'listar'])->name('clientes.listar');
//     Route::get('/clientes/crear', [ClienteController::class, 'vistaFormulario'])->name('clientes.formulario');
//     Route::post('/clientes/guardar', [ClienteController::class, 'registrar'])->name('clientes.registrar');
//     Route::get('/clientes/editar/{id}', [ClienteController::class, 'vistaEdicion'])->name('clientes.edicion');
//     Route::put('/clientes/actualizar/{id}', [ClienteController::class, 'actualizar'])->name('clientes.actualizar');
//     Route::get('/clientes/mostrar/{id}', [ClienteController::class, 'vistaMostrar'])->name('clientes.mostrar');
//     Route::delete('/clientes/borrar/{id}', [ClienteController::class, 'borrar'])->name('clientes.borrar');

//     Route::get('/productos/listar', [ProductoController::class, 'listar'])->name('productos.listar');
//     Route::get('/productos/crear', [ProductoController::class, 'vistaFormulario'])->name('productos.formulario');
//     Route::post('/productos/guardar', [ProductoController::class, 'registrar'])->name('productos.registrar');
//     Route::get('/productos/editar/{id}', [ProductoController::class, 'vistaEdicion'])->name('productos.edicion');
//     Route::put('/productos/actualizar/{id}', [ProductoController::class, 'actualizar'])->name('productos.actualizar');
//     Route::get('/productos/mostrar/{id}', [ProductoController::class, 'vistaMostrar'])->name('productos.mostrar');
//     Route::delete('/productos/borrar/{id}', [ProductoController::class, 'borrar'])->name('productos.borrar');

//     Route::get('/proveedores/listar', [ProveedorController::class, 'listar'])->name('proveedores.listar');
//     Route::get('/proveedores/crear', [ProveedorController::class, 'vistaFormulario'])->name('proveedores.formulario');
//     Route::post('/proveedores/guardar', [ProveedorController::class, 'registrar'])->name('proveedores.registrar');
//     Route::get('/proveedores/editar/{id}', [ProveedorController::class, 'vistaEdicion'])->name('proveedores.edicion');
//     Route::put('/proveedores/actualizar/{id}', [ProveedorController::class, 'actualizar'])->name('proveedores.actualizar');
//     Route::get('/proveedores/mostrar/{id}', [ProveedorController::class, 'vistaMostrar'])->name('proveedores.mostrar');
//     Route::delete('/proveedores/borrar/{id}', [ProveedorController::class, 'borrar'])->name('proveedores.borrar');
// });
