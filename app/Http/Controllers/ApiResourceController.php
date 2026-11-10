<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Coleccionable;
use App\Models\Condicion;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Universo;
use App\Models\Venta;
use App\Services\CloudinaryImageStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiResourceController extends Controller
{
    private const RESOURCES = [
        'administradores' => [
            'model' => Administrador::class,
            'rules' => [
                'nombres' => 'required|string|max:255',
                'apellidos' => 'required|string|max:255',
                'correo' => 'required|email|max:255|unique:administradores,correo',
                'usuario' => 'required|string|max:50|unique:administradores,usuario',
                'contraseña' => 'required|string|min:8',
                'rol' => 'required|string|max:255',
                'estado' => 'sometimes|boolean',
            ],
            'images' => ['imagen' => false],
        ],
        'categorias' => [
            'model' => Categoria::class,
            'rules' => [
                'nombre' => 'required|string|max:255|unique:categorias,nombre',
                'estado' => 'sometimes|boolean',
            ],
            'images' => ['imagen' => false],
        ],
        'clientes' => [
            'model' => Cliente::class,
            'rules' => [
                'nombres' => 'required|string|max:255',
                'apellidos' => 'required|string|max:255',
                'correo' => 'required|email|max:255|unique:clientes,correo',
                'contraseña' => 'required|string|min:8',
                'direccion' => 'nullable|string',
                'estado' => 'sometimes|boolean',
            ],
            'images' => ['imagen' => false],
        ],
        'coleccionables' => [
            'model' => Coleccionable::class,
            'rules' => [
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'categoria_id' => 'required|integer|exists:categorias,id',
                'universo_id' => 'required|integer|exists:universos,id',
                'condicion_id' => 'required|integer|exists:condiciones,id',
                'precio' => 'required|numeric|min:0',
                'existencia' => 'sometimes|integer|min:0',
                'descuento' => 'sometimes|numeric|min:0|max:100',
                'estado' => 'sometimes|boolean',
            ],
            'images' => ['imagen1' => false, 'imagen2' => false, 'imagen3' => false],
        ],
        'condiciones' => [
            'model' => Condicion::class,
            'rules' => [
                'nombre' => 'required|string|max:255|unique:condiciones,nombre',
                'estado' => 'sometimes|boolean',
            ],
            'images' => ['imagen' => false],
        ],
        'detalles_pedidos' => [
            'model' => DetallePedido::class,
            'rules' => [
                'pedido_id' => 'required|integer|exists:pedidos,id',
                'coleccionable_id' => 'required|integer|exists:coleccionables,id',
                'cantidad' => 'required|integer|min:1',
                'precio' => 'required|numeric|min:0',
                'descuento' => 'sometimes|numeric|min:0',
            ],
            'images' => [],
        ],
        'pedidos' => [
            'model' => Pedido::class,
            'rules' => [
                'cliente_id' => 'required|integer|exists:clientes,id',
                'fecha' => 'required|date',
                'iva' => 'required|numeric|min:0',
                'descuento' => 'sometimes|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'estado' => 'sometimes|string|max:255',
            ],
            'images' => [],
        ],
        'productos' => [
            'model' => Producto::class,
            'rules' => [
                'categoria_id' => 'required|integer|exists:categorias,id',
                'proveedor_id' => 'required|integer|exists:proveedores,id',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'precio' => 'required|numeric|min:0',
                'existencia' => 'sometimes|integer|min:0',
                'marca' => 'required|string|max:255',
                'estado' => 'sometimes|boolean',
            ],
            'images' => ['imagen' => true],
        ],
        'proveedores' => [
            'model' => Proveedor::class,
            'rules' => [
                'nombre_empresa' => 'required|string|max:255',
                'contacto' => 'required|string|max:255',
                'correo' => 'required|email|max:255|unique:proveedores,correo',
                'telefono' => 'required|string|max:255',
                'direccion' => 'required|string|max:255',
                'ciudad' => 'required|string|max:255',
                'estado' => 'sometimes|boolean',
            ],
            'images' => ['imagen' => true],
        ],
        'universos' => [
            'model' => Universo::class,
            'rules' => [
                'nombre' => 'required|string|max:255|unique:universos,nombre',
                'estado' => 'sometimes|boolean',
            ],
            'images' => ['imagen' => false],
        ],
        'ventas' => [
            'model' => Venta::class,
            'rules' => [
                'cliente_id' => 'required|integer|exists:clientes,id',
                'producto_id' => 'required|integer|exists:productos,id',
                'fecha_venta' => 'required|date',
                'subtotal' => 'required|numeric|min:0',
                'impuesto' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'metodo_pago' => 'required|string|max:255',
                'observaciones' => 'nullable|string|max:255',
                'estado' => 'sometimes|boolean',
            ],
            'images' => [],
        ],
    ];

    public function index(Request $request, string $resource): JsonResponse
    {
        $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $model = $this->modelFor($resource);
        $records = $model::query()->latest('id')->paginate($request->integer('per_page', 15));

        return response()->json($records);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $config = $this->configurationFor($resource);
        $data = $this->validatedData($request, $config);
        $record = $this->modelFor($resource)::query()->create($this->prepareData($request, $data, $config));

        return response()->json([
            'message' => 'Registro creado correctamente.',
            'data' => $record,
        ], 201);
    }

    public function show(string $resource, int $id): JsonResponse
    {
        $record = $this->modelFor($resource)::query()->findOrFail($id);

        return response()->json(['data' => $record]);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $config = $this->configurationFor($resource);
        $record = $this->modelFor($resource)::query()->findOrFail($id);
        $data = $this->validatedData($request, $config, $id);
        $oldImages = $this->imageValues($record, $config);
        $record->update($this->prepareData($request, $data, $config));

        $this->deleteReplacedImages($record, $oldImages, $config);

        return response()->json([
            'message' => 'Registro actualizado correctamente.',
            'data' => $record->fresh(),
        ]);
    }

    public function destroy(string $resource, int $id): JsonResponse
    {
        $config = $this->configurationFor($resource);
        $record = $this->modelFor($resource)::query()->findOrFail($id);
        $oldImages = $this->imageValues($record, $config);
        $record->delete();

        foreach ($oldImages as $image) {
            app(CloudinaryImageStorage::class)->delete($image);
        }

        return response()->json(['message' => 'Registro eliminado correctamente.']);
    }

    private function validatedData(Request $request, array $config, ?int $id = null): array
    {
        $rules = [];

        foreach ($config['rules'] as $field => $rule) {
            if ($id !== null) {
                $parts = explode('|', $rule);
                $uniqueRule = null;

                foreach ($parts as $index => $part) {
                    if ($part === 'required') {
                        $parts[$index] = 'sometimes';
                        $parts[] = 'required';
                    } elseif (str_starts_with($part, 'unique:')) {
                        [$table, $column] = explode(',', substr($part, strlen('unique:')), 2);
                        $uniqueRule = Rule::unique($table, $column)->ignore($id);
                        unset($parts[$index]);
                    }
                }

                $parts = array_values($parts);

                if ($uniqueRule !== null) {
                    $parts[] = $uniqueRule;
                    $rule = $parts;
                } else {
                    $rule = implode('|', $parts);
                }
            }

            $rules[$field] = $rule;
        }

        foreach ($config['images'] as $field => $required) {
            $presenceRule = $id !== null ? 'sometimes|' : ($required ? 'required|' : 'nullable|');
            $rules[$field] = [
                ...explode('|', rtrim($presenceRule, '|')),
                $request->file($field) instanceof UploadedFile ? 'image' : 'string',
                ...($request->file($field) instanceof UploadedFile
                    ? ['mimes:jpg,jpeg,png,webp', 'max:2048']
                    : ['max:2048']),
            ];
        }

        return Validator::make($request->all(), $rules)->validate();
    }

    private function prepareData(Request $request, array $data, array $config): array
    {
        foreach ($config['images'] as $field => $required) {
            if ($request->file($field) instanceof UploadedFile) {
                $data[$field] = app(CloudinaryImageStorage::class)->upload(
                    $request->file($field),
                    (string) $request->route('resource')
                );
            }
        }

        if (array_key_exists('contraseña', $data)) {
            $data['contraseña'] = Hash::make($data['contraseña']);
        }

        return $data;
    }

    private function deleteReplacedImages(Model $record, array $oldImages, array $config): void
    {
        foreach ($oldImages as $field => $oldImage) {
            if ($record->{$field} !== $oldImage) {
                app(CloudinaryImageStorage::class)->delete($oldImage);
            }
        }
    }

    private function imageValues(Model $record, array $config): array
    {
        $images = [];

        foreach ($config['images'] as $field => $required) {
            $images[$field] = $record->{$field};
        }

        return $images;
    }

    private function modelFor(string $resource): string
    {
        return $this->configurationFor($resource)['model'];
    }

    private function configurationFor(string $resource): array
    {
        abort_unless(isset(self::RESOURCES[$resource]), 404, 'Recurso API no encontrado.');

        return self::RESOURCES[$resource];
    }
}
