<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## CRUD

La aplicación incluye un controlador por cada modelo del proyecto: administradores, categorías, clientes, coleccionables, condiciones, detalles de pedidos, pedidos, productos, proveedores, universos y ventas.

Cada módulo expone manualmente las rutas `listar`, `crear`, `guardar`, `editar/{id}`, `actualizar/{id}`, `mostrar/{id}` y `borrar/{id}` con los verbos HTTP GET, POST, PUT y DELETE. Las cuatro vistas por módulo están dentro de `resources/views/<modulo>/mvc` y reutilizan plantillas de presentación en `resources/views/crud`.

Cada módulo consulta y modifica sus registros mediante Eloquent. Los selects relacionados se cargan desde la base de datos, y los formularios validan los datos antes de crear o actualizar. Configura la conexión definida en `.env` y ejecuta `php artisan migrate` antes de usar los módulos.

## API REST para Postman

La API administrativa requiere un token de Sanctum. Inicia sesión con `POST /api/login`, enviando `Accept: application/json` y el JSON `{"usuario":"...","contraseña":"..."}`. Copia `access_token` de la respuesta y envíalo como `Authorization: Bearer <token>` en las siguientes peticiones. `POST /api/logout` revoca únicamente ese token.

Todos los recursos cuentan con `GET /api/<recurso>`, `POST /api/<recurso>`, `GET /api/<recurso>/{id}`, `PUT` o `PATCH /api/<recurso>/{id}` y `DELETE /api/<recurso>/{id}`. Los recursos son `administradores`, `categorias`, `clientes`, `coleccionables`, `condiciones`, `detalles_pedidos`, `pedidos`, `productos`, `proveedores`, `universos` y `ventas`. Las respuestas son JSON; los errores de validación devuelven los campos que deben corregirse. Las listas están paginadas y aceptan `?per_page=1` a `?per_page=100`.

Para imágenes, envía el archivo en `form-data` con el nombre del campo correspondiente (`imagen`, `imagen1`, `imagen2` o `imagen3`); también se aceptan referencias de imagen en texto. Los archivos se guardan en Cloudinary, por lo que para subirlos configura sus variables en `.env`.

### Crear y conectar la base de datos local (XAMPP)

Inicia MySQL desde el panel de XAMPP. Entra a phpMyAdmin, crea una base llamada `proyectooc` con cotejamiento `utf8mb4_unicode_ci` y configura la conexión en el `.env` de este proyecto. Para la instalación local típica de XAMPP:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=proyectooc
DB_USERNAME=root
DB_PASSWORD=
```

Si tu usuario MySQL tiene contraseña, coloca su valor en `DB_PASSWORD`. Después ejecuta `php artisan config:clear` y `php artisan migrate` para crear las tablas. `migrate` conserva las tablas y datos existentes; evita `migrate:fresh`, porque elimina las tablas. Crea un administrador activo para el primer inicio de sesión con `php artisan api:admin`; el comando solicita sus datos, oculta la contraseña y la pide dos veces para confirmarla.

## Autenticación administrativa

El acceso al panel y a las rutas CRUD se realiza con el guard de sesión `admin`, basado en el modelo `Administrador`. El formulario está en `/login`; usa los campos `usuario` y `contraseña`, y solo permite cuentas con `estado = 1`. El CRUD cifra las contraseñas con `Hash::make()` al guardarlas. Para ingresar por primera vez, debe existir un administrador activo creado desde la base de datos o por el proceso de inicialización del proyecto.

También se puede iniciar sesión con Google mediante Laravel Socialite. Si XAMPP sirve este proyecto desde `http://localhost/proyectooc/public`, configura estas variables en `.env`. En Google Cloud Console, configura la pantalla de consentimiento OAuth y crea un ID de cliente OAuth para aplicación web:

```dotenv
APP_URL=http://localhost/proyectooc/public
GOOGLE_CLIENT_ID=tu_client_id
GOOGLE_CLIENT_SECRET=tu_client_secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

Registra como URI de redireccionamiento autorizada exactamente `http://localhost/proyectooc/public/auth/google/callback` y abre el formulario en `http://localhost/proyectooc/public/login`. El inicio se hace con el botón de Google y el proveedor retorna a `/auth/google/callback`; no abras el callback directamente. Si el correo verificado corresponde a un administrador activo, conserva su acceso al panel. En otro caso, registra o inicia sesión como cliente, guarda el identificador de Google y redirige al inicio de la tienda. Ejecuta `php artisan migrate` para agregar `google_id` a la tabla `clientes`. Las cuentas desactivadas no pueden iniciar sesión. Si configuras un VirtualHost con la carpeta `public` como raíz o usas otro puerto/dominio, ajusta `APP_URL` y registra en Google la URI de callback correspondiente a ese entorno.

## Imágenes en Cloudinary

Las imágenes que se cargan desde los módulos de administradores, categorías, clientes, productos y proveedores se suben desde Laravel al SDK PHP oficial de Cloudinary. En `.env`, configura `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY` y `CLOUDINARY_API_SECRET` con los datos de tu consola de Cloudinary; no publiques ni compartas el API secret. Las referencias nuevas se guardan como identificadores de Cloudinary, mientras que las imágenes locales que ya estaban registradas se siguen mostrando desde `storage`.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
