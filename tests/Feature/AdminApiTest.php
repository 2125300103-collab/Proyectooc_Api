<?php

namespace Tests\Feature;

use App\Models\Administrador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_token_can_run_the_category_crud(): void
    {
        Administrador::query()->create([
            'nombres' => 'Admin',
            'apellidos' => 'Local',
            'correo' => 'admin@example.test',
            'usuario' => 'admin',
            'contraseña' => Hash::make('password123'),
            'rol' => 'Administrador',
            'estado' => true,
        ]);

        $token = $this->postJson('/api/login', [
            'usuario' => 'admin',
            'contraseña' => 'password123',
        ])->assertOk()->json('access_token');

        foreach ([
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
        ] as $resource) {
            $this->withToken($token)->getJson('/api/'.$resource)->assertOk();
        }

        $created = $this->withToken($token)
            ->postJson('/api/categorias', ['nombre' => 'Accesorios'])
            ->assertCreated()
            ->assertJsonPath('data.nombre', 'Accesorios');

        $id = $created->json('data.id');

        $this->withToken($token)
            ->getJson('/api/categorias/'.$id)
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Accesorios');

        $this->withToken($token)
            ->putJson('/api/categorias/'.$id, ['nombre' => 'Coleccionables'])
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Coleccionables');

        $this->withToken($token)
            ->deleteJson('/api/categorias/'.$id)
            ->assertOk();

        $this->withToken($token)
            ->getJson('/api/categorias/'.$id)
            ->assertNotFound();

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
        Auth::forgetGuards();

        $this->withToken($token)
            ->getJson('/api/categorias')
            ->assertUnauthorized();
    }

    public function test_admin_token_can_run_crud_for_every_api_resource(): void
    {
        Administrador::query()->create([
            'nombres' => 'Admin',
            'apellidos' => 'Local',
            'correo' => 'admin@example.test',
            'usuario' => 'admin',
            'contraseña' => Hash::make('password123'),
            'rol' => 'Administrador',
            'estado' => true,
        ]);

        $token = $this->postJson('/api/login', [
            'usuario' => 'admin',
            'contraseña' => 'password123',
        ])->assertOk()->json('access_token');

        $create = function (string $resource, array $data) use ($token): int {
            $response = $this->withToken($token)
                ->postJson('/api/'.$resource, $data)
                ->assertCreated();

            return $response->json('data.id');
        };

        $updateAndRead = function (string $resource, int $id, array $data) use ($token): void {
            $this->withToken($token)
                ->putJson('/api/'.$resource.'/'.$id, $data)
                ->assertOk();

            $this->withToken($token)
                ->getJson('/api/'.$resource.'/'.$id)
                ->assertOk();
        };

        $ids = [];
        $ids['categorias'] = $create('categorias', ['nombre' => 'Accesorios']);
        $ids['universos'] = $create('universos', ['nombre' => 'Universo API']);
        $ids['condiciones'] = $create('condiciones', ['nombre' => 'Nueva']);
        $ids['clientes'] = $create('clientes', [
            'nombres' => 'Cliente',
            'apellidos' => 'API',
            'correo' => 'cliente-api@example.test',
            'contraseña' => 'cliente12345',
        ]);
        $ids['proveedores'] = $create('proveedores', [
            'nombre_empresa' => 'Proveedor API',
            'contacto' => 'Contacto',
            'correo' => 'proveedor-api@example.test',
            'telefono' => '5551234567',
            'direccion' => 'Calle de prueba',
            'ciudad' => 'Ciudad',
            'imagen' => 'proveedor.jpg',
        ]);
        $ids['productos'] = $create('productos', [
            'categoria_id' => $ids['categorias'],
            'proveedor_id' => $ids['proveedores'],
            'nombre' => 'Producto API',
            'descripcion' => 'Descripción de prueba',
            'precio' => 100,
            'existencia' => 5,
            'marca' => 'Marca',
            'imagen' => 'producto.jpg',
        ]);
        $ids['coleccionables'] = $create('coleccionables', [
            'nombre' => 'Coleccionable API',
            'categoria_id' => $ids['categorias'],
            'universo_id' => $ids['universos'],
            'condicion_id' => $ids['condiciones'],
            'precio' => 200,
        ]);
        $ids['pedidos'] = $create('pedidos', [
            'cliente_id' => $ids['clientes'],
            'fecha' => '2026-11-02 12:00:00',
            'iva' => 16,
            'total' => 116,
        ]);
        $ids['detalles_pedidos'] = $create('detalles_pedidos', [
            'pedido_id' => $ids['pedidos'],
            'coleccionable_id' => $ids['coleccionables'],
            'cantidad' => 1,
            'precio' => 200,
        ]);
        $ids['ventas'] = $create('ventas', [
            'cliente_id' => $ids['clientes'],
            'producto_id' => $ids['productos'],
            'fecha_venta' => '2026-11-02 12:00:00',
            'subtotal' => 100,
            'impuesto' => 16,
            'total' => 116,
            'metodo_pago' => 'efectivo',
        ]);
        $ids['administradores'] = $create('administradores', [
            'nombres' => 'Otro',
            'apellidos' => 'Administrador',
            'correo' => 'otro-admin@example.test',
            'usuario' => 'otro-admin',
            'contraseña' => 'adminpassword123',
            'rol' => 'Inventario',
        ]);

        foreach ([
            'categorias' => ['nombre' => 'Accesorios editados'],
            'universos' => ['nombre' => 'Universo editado'],
            'condiciones' => ['nombre' => 'Condición editada'],
            'clientes' => ['nombres' => 'Cliente editado'],
            'proveedores' => ['contacto' => 'Contacto editado'],
            'productos' => ['nombre' => 'Producto editado'],
            'coleccionables' => ['nombre' => 'Coleccionable editado'],
            'pedidos' => ['estado' => 'pagado'],
            'detalles_pedidos' => ['cantidad' => 2],
            'ventas' => ['observaciones' => 'Venta editada'],
            'administradores' => ['nombres' => 'Admin editado'],
        ] as $resource => $data) {
            $this->withToken($token)
                ->getJson('/api/'.$resource)
                ->assertOk();

            $this->withToken($token)
                ->getJson('/api/'.$resource.'/'.$ids[$resource])
                ->assertOk();

            $updateAndRead($resource, $ids[$resource], $data);
        }

        foreach ([
            'ventas',
            'detalles_pedidos',
            'coleccionables',
            'productos',
            'pedidos',
            'clientes',
            'proveedores',
            'categorias',
            'universos',
            'condiciones',
            'administradores',
        ] as $resource) {
            $this->withToken($token)
                ->deleteJson('/api/'.$resource.'/'.$ids[$resource])
                ->assertOk();
        }
    }

    public function test_api_resources_reject_requests_without_an_admin_token(): void
    {
        $this->getJson('/api/categorias')->assertUnauthorized();
    }
}
