<?php

namespace Tests\Feature;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IntegrationPracticeTest extends TestCase
{
    use RefreshDatabase;

    private function getAuthenticatedAdminToken(): string
    {
        Administrador::query()->create([
            'nombres' => 'Admin',
            'apellidos' => 'Pruebas',
            'correo' => 'admin@proyectooc.test',
            'usuario' => 'admin_test',
            'contraseña' => Hash::make('password123'),
            'rol' => 'Administrador',
            'estado' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'usuario' => 'admin_test',
            'contraseña' => 'password123',
        ]);

        $response->assertOk();
        return $response->json('access_token');
    }

    /**
     * CASO DE PRUEBA 1: Registro de información y comprobación en base de datos.
     */
    public function test_1_registro_de_informacion_en_base_de_datos(): void
    {
        $token = $this->getAuthenticatedAdminToken();

        $datosCategoria = [
            'nombre' => 'Figuras y Coleccionables',
            'estado' => true,
        ];

        $response = $this->withToken($token)->postJson('/api/categorias', $datosCategoria);

        $response->assertCreated()
            ->assertJsonPath('data.nombre', 'Figuras y Coleccionables');

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Figuras y Coleccionables',
        ]);
    }

    /**
     * CASO DE PRUEBA 2: Registrar un usuario e iniciar sesión con sus credenciales.
     */
    public function test_2_inicio_de_sesion_con_credenciales_de_usuario(): void
    {
        Administrador::query()->create([
            'nombres' => 'Carlos',
            'apellidos' => 'López',
            'correo' => 'carlos.admin@proyectooc.test',
            'usuario' => 'carlos_admin',
            'contraseña' => Hash::make('secreto2026'),
            'rol' => 'Administrador',
            'estado' => true,
        ]);

        $this->assertDatabaseHas('administradores', [
            'usuario' => 'carlos_admin',
            'correo' => 'carlos.admin@proyectooc.test',
        ]);

        $response = $this->postJson('/api/login', [
            'usuario' => 'carlos_admin',
            'contraseña' => 'secreto2026',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['access_token', 'token_type']);

        $token = $response->json('access_token');
        $this->assertNotEmpty($token);

        $this->withToken($token)
            ->getJson('/api/administradores')
            ->assertOk();
    }

    /**
     * CASO DE PRUEBA 3: Registrar un producto y posteriormente consultarlo desde otro módulo.
     */
    public function test_3_registro_de_producto_y_consulta_en_catalogo(): void
    {
        $token = $this->getAuthenticatedAdminToken();

        $categoria = Categoria::query()->create([
            'nombre' => 'Cómics y Manga',
            'estado' => true,
        ]);

        $proveedor = Proveedor::query()->create([
            'nombre_empresa' => 'Distribuidora Panini',
            'contacto' => 'Juan Pérez',
            'correo' => 'ventas@panini.test',
            'telefono' => '5559876543',
            'direccion' => 'Av. Insurgentes 450',
            'ciudad' => 'CDMX',
            'imagen' => 'proveedor_panini.jpg',
            'estado' => true,
        ]);

        $datosProducto = [
            'categoria_id' => $categoria->id,
            'proveedor_id' => $proveedor->id,
            'nombre' => 'Spider-Man Cómic Tomo 1',
            'descripcion' => 'Edición especial de colección pasta dura',
            'precio' => 350.00,
            'existencia' => 20,
            'marca' => 'Marvel',
            'imagen' => 'spiderman_tomo1.jpg',
            'estado' => true,
        ];

        $crearResponse = $this->withToken($token)->postJson('/api/productos', $datosProducto);
        $crearResponse->assertCreated();
        $productoId = $crearResponse->json('data.id');

        $consultaResponse = $this->withToken($token)->getJson('/api/productos/' . $productoId);
        $consultaResponse->assertOk()
            ->assertJsonPath('data.id', $productoId)
            ->assertJsonPath('data.nombre', 'Spider-Man Cómic Tomo 1')
            ->assertJsonPath('data.precio', 350);

        $this->assertDatabaseHas('productos', [
            'id' => $productoId,
            'categoria_id' => $categoria->id,
            'proveedor_id' => $proveedor->id,
        ]);
    }

    /**
     * CASO DE PRUEBA 4: Modificar un registro y comprobar que los cambios se reflejen en la base de datos.
     */
    public function test_4_modificacion_de_registro_y_comprobacion_en_bd(): void
    {
        $token = $this->getAuthenticatedAdminToken();

        $categoria = Categoria::query()->create(['nombre' => 'Juegos de Mesa', 'estado' => true]);
        $proveedor = Proveedor::query()->create([
            'nombre_empresa' => 'Hasbro México',
            'contacto' => 'Laura Gómez',
            'correo' => 'contacto@hasbro.test',
            'telefono' => '5554321098',
            'direccion' => 'Parque Industrial Norte',
            'ciudad' => 'Guadalajara',
            'imagen' => 'hasbro.jpg',
            'estado' => true,
        ]);

        $producto = Producto::query()->create([
            'categoria_id' => $categoria->id,
            'proveedor_id' => $proveedor->id,
            'nombre' => 'Monopoly Clásico',
            'descripcion' => 'Juego de mesa familiar',
            'precio' => 450.00,
            'existencia' => 15,
            'marca' => 'Hasbro',
            'imagen' => 'monopoly.jpg',
            'estado' => true,
        ]);

        $datosActualizados = [
            'nombre' => 'Monopoly Edición Coleccionista',
            'precio' => 599.99,
            'existencia' => 8,
        ];

        $response = $this->withToken($token)->putJson('/api/productos/' . $producto->id, $datosActualizados);
        $response->assertOk()
            ->assertJsonPath('data.nombre', 'Monopoly Edición Coleccionista')
            ->assertJsonPath('data.precio', 599.99);

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'nombre' => 'Monopoly Edición Coleccionista',
            'precio' => 599.99,
            'existencia' => 8,
        ]);

        $this->assertDatabaseMissing('productos', [
            'id' => $producto->id,
            'nombre' => 'Monopoly Clásico',
        ]);
    }

    /**
     * CASO DE PRUEBA 5: Eliminar un registro y comprobar que ya no aparezca en las consultas.
     */
    public function test_5_eliminacion_de_registro_y_comprobacion_de_no_existencia(): void
    {
        $token = $this->getAuthenticatedAdminToken();

        $categoria = Categoria::query()->create([
            'nombre' => 'Categoría Temporal a Borrar',
            'estado' => true,
        ]);

        $categoriaId = $categoria->id;

        $this->assertDatabaseHas('categorias', ['id' => $categoriaId]);

        $deleteResponse = $this->withToken($token)->deleteJson('/api/categorias/' . $categoriaId);
        $deleteResponse->assertOk();

        $this->assertDatabaseMissing('categorias', ['id' => $categoriaId]);

        $this->withToken($token)
            ->getJson('/api/categorias/' . $categoriaId)
            ->assertNotFound();
    }
}
