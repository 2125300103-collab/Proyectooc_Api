<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Services\CloudinaryImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    protected string $module = 'productos';
    protected string $moduleTitle = 'Productos';
    protected string $modelClass = Producto::class;
    protected array $fields = [
        ['name' => 'categoria_id', 'label' => 'Categoría', 'type' => 'select', 'required' => true],
        ['name' => 'proveedor_id', 'label' => 'Proveedor', 'type' => 'select', 'required' => true],
        ['name' => 'nombre', 'label' => 'Nombre', 'required' => true],
        ['name' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'required' => true],
        ['name' => 'precio', 'label' => 'Precio', 'type' => 'number', 'required' => true, 'rules' => ['min:0']],
        ['name' => 'existencia', 'label' => 'Existencia', 'type' => 'number', 'required' => true, 'rules' => ['integer', 'min:0']],
        ['name' => 'marca', 'label' => 'Marca', 'required' => true],
        ['name' => 'estado', 'label' => 'Estado', 'type' => 'select', 'required' => true, 'options' => ['1' => 'Activo', '0' => 'Inactivo']],
        ['name' => 'imagen', 'label' => 'Imagen', 'type' => 'image', 'required' => true],
    ];
    protected array $optionSources = [
        'categoria_id' => [Categoria::class, 'nombre'],
        'proveedor_id' => [Proveedor::class, 'nombre_empresa'],
    ];

    public function listar(): JsonResponse
    {
        $registros = Producto::query()->latest()->limit(100)->get()->toArray();

        return response()->json($this->datosVista(['registros' => $registros]));
    }

    public function vistaFormulario(): JsonResponse
    {
        return response()->json($this->datosVista());
    }

    public function registrar(Request $request): JsonResponse
    {
        $datos = Validator::make($request->all(), $this->reglasValidacion())->validate();
        $registro = Producto::create($this->prepararDatos($datos));

        return response()->json([
            'message' => 'Producto creado correctamente.',
            'producto' => $registro,
        ], 201);
    }

    public function vistaEdicion(int $id): JsonResponse
    {
        $registro = Producto::query()->findOrFail($id);

        return response()->json($this->datosVista([
            'id' => $id,
            'registro' => $registro->toArray(),
        ]));
    }

    public function actualizar(Request $request, int $id): JsonResponse
    {
        $registro = Producto::query()->findOrFail($id);
        $datos = Validator::make($request->all(), $this->reglasValidacion(true))->validate();
        $imagenAnterior = $registro->imagen;
        $registro->update($this->prepararDatos($datos));

        if ($registro->imagen !== $imagenAnterior) {
            app(CloudinaryImageStorage::class)->delete($imagenAnterior);
        }

        return response()->json([
            'message' => 'Producto actualizado correctamente.',
            'producto' => $registro->fresh(),
        ]);
    }

    public function vistaMostrar(int $id): JsonResponse
    {
        $registro = Producto::query()->findOrFail($id);

        return response()->json($this->datosVista([
            'id' => $id,
            'registro' => $registro->toArray(),
        ]));
    }

    public function borrar(int $id): JsonResponse
    {
        $registro = Producto::query()->findOrFail($id);
        $registro->update(['estado' => false]);

        return response()->json(['message' => 'Producto desactivado correctamente.']);
    }

    private function datosVista(array $extra = []): array
    {
        $campos = $this->fields;

        foreach ($campos as &$campo) {
            $campo['options'] = $this->opcionesCampo($campo['name'], $campo['options'] ?? []);
        }
        unset($campo);

        return array_merge([
            'modulo' => $this->module,
            'titulo' => $this->moduleTitle,
            'modelo' => 'Producto',
            'campos' => $campos,
            'registro' => [],
        ], $extra);
    }

    private function reglasValidacion(bool $actualizacion = false): array
    {
        $required = $actualizacion ? 'sometimes|required' : 'required';

        return [
            'categoria_id' => [$required, 'integer', 'exists:categorias,id'],
            'proveedor_id' => [$required, 'integer', 'exists:proveedores,id'],
            'nombre' => [$required, 'string', 'max:255'],
            'descripcion' => [$required, 'string'],
            'precio' => [$required, 'numeric', 'min:0'],
            'existencia' => [$required, 'integer', 'min:0'],
            'marca' => [$required, 'string', 'max:255'],
            'estado' => [$required, 'boolean'],
            'imagen' => [
                $actualizacion ? 'sometimes' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }

    private function prepararDatos(array $datos): array
    {
        foreach ($this->fields as $campo) {
            $nombre = $campo['name'];

            if (($campo['type'] ?? '') === 'image') {
                if (!isset($datos[$nombre])) {
                    unset($datos[$nombre]);
                    continue;
                }

                $datos[$nombre] = app(CloudinaryImageStorage::class)->upload($datos[$nombre], $this->module);
            }

            if (isset($campo['column']) && array_key_exists($nombre, $datos)) {
                if (($campo['type'] ?? '') === 'password') {
                    if (blank($datos[$nombre])) {
                        unset($datos[$nombre]);
                        continue;
                    }

                    $datos[$nombre] = Hash::make($datos[$nombre]);
                }

                $datos[$campo['column']] = $datos[$nombre];
                unset($datos[$nombre]);
            }
        }

        return $datos;
    }

    private function opcionesCampo(string $nombre, array $opciones = []): array
    {
        if (!isset($this->optionSources[$nombre])) {
            return $opciones;
        }

        [$modelo, $columna] = $this->optionSources[$nombre];

        return $modelo::query()->orderBy($columna)->pluck($columna, 'id')->all();
    }
}