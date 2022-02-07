# TUTORIAL "API-REST" CON LARAVEL. LECCIONES DE 1 A 48 (SECCIONES 1 A 14).

## EJECUTAR LA APP en Localhost:

Se requiere MAMP con un servidor MySQL arrancado y la BD "ap_rest_laravel":

[http://localhost/phpmyadmin/db_structure.php?server=1&db=api_rest_laravel&token=c41d2e17d8de2350ef7c6760c6b90586](http://localhost/phpmyadmin/db_structure.php?server=1&db=api_rest_laravel&token=c41d2e17d8de2350ef7c6760c6b90586)


> (En Terminal): `php artisan serve` 

Eso correrá la app en el puerto 8000: [http://localhost:8000](http://localhost:8000/api/category) (NOTA: No tiene frontend porque es una API-REST)

### Algunas pruebas en el navegador web o en **POSTMAN**:
Para ver todas las posibles rutas, mirar la sección "Crear RUTAS".

* [http://localhost:8000/api/post/category/1](http://localhost:8000/api/category)
* [http://localhost:8000/api/user/avatar/1643570696bebe.jpg](http://localhost:8000/api/category)
* [http://localhost:8000/api/user/getUserById/1](http://localhost:8000/api/category)
* [http://localhost:8000/api/post/2](http://localhost:8000/api/category)
* [http://localhost:8000/api/category](http://localhost:8000/api/category)

### Documentación:

* [https://styde.net/documentacion-de-laravel-6/](https://styde.net/documentacion-de-laravel-6/)
* [https://learninglaravel.net/cheatsheet/](https://learninglaravel.net/cheatsheet/)
* [https://simplecheatsheet.com/tag/laravel-cheat-sheet/](https://simplecheatsheet.com/tag/laravel-cheat-sheet/)
* 




### Requisitos:
- MAMP
- COMPOSER
- LARAVEL

### Instalar COMPOSER (via $BREW):
> `brew install composer`


### Creación del proyecto Laravel:
composer create-project [laravel repo] [name-of-project] [laravel version] [params]

> `composer create-project laravel/laravel api-rest-laravel "8.*" --prefer-dist`

### (Opcional) Crear host virtual en "httpd-vhosts.conf". Más info: 
[https://victorroblesweb.es/2016/03/26/crear-varios-hosts-virtuales-en-wampserver/]()

# Primeros pasos con Laravel:

####  Crear CONTROLLER en **app/Http/Controllers/**: (terminal)

> (Terminal) `php artisan make:controller nombreController`


#### Crear VIEW en **/resources/views/**: "nombreVista.blade.php" (BLADE es el motor de plantillas).

> (Terminal) `php artisan make:view nombreVista`


#### Crear MODEL. En Laravel 8.x, se crean en **app/Models/**:

> (Terminal) `php artisan make:model nombreModel`


#### Crear SERVICE PROVIDER  en **app/Providers/**:

> (Terminal) `php artisan make:provider JwtAuthServiceProvider`
>
> NOTA: Para usar el Provider, hay que agregarlo a **"config/app.php**" en el array '**providers**':
>
> `App\Providers\JwtAuthServiceProvider::class`
>
> También hay que agregar nuestro Helper en el array '**alias**' del mismo archivo:
>
> `'JwtAuth' => \App\Helpers\JwtAuth::class`


#### Crear MIDDLEWARE (que se ejecutará antes de las acciones del controller). En **app/Http/Middleware/**:

> `php artisan make:middleware ApiAuthMiddleware`
> 
> También hay que registrar la ruta en el **"app/Http/Kernel.php"**, en "protected routeMiddleware":
> 
> `'api.auth' => \App\Http\Middleware\ApiAuthMiddleware::class`



#### Crear RUTAS del API en **/routes/web.php**:

> `Route::get ('/entrada/test', 'App\Http\Controllers\PostController@test');`
>
> (test navegador): [http://localhost:8000/entrada/pruebas](http://localhost:8000/entrada/pruebas)

```php
// USERS:
Route::get  ('/user/register',    'App\Http\Controllers\UserController@register');
Route::get  ('/user/login',       'App\Http\Controllers\UserController@login');
Route::post ('/api/register',     'App\Http\Controllers\UserController@register');
Route::post ('/api/login',        'App\Http\Controllers\UserController@login');
Route::put  ('/api/user/update',  'App\Http\Controllers\UserController@update');
Route::get  ('/api/user/avatar/{filename}',  'App\Http\Controllers\UserController@getImage'); 
Route::get  ('/api/user/getUserById/{id}',  'App\Http\Controllers\UserController@getUserById');
// CATEGORIES:
Route::resource('/api/category', 'App\Http\Controllers\CategoryController');
// POSTS:
Route::resource('/api/post', 'App\Http\Controllers\PostController');
Route::post ('/api/post/upload', 'App\Http\Controllers\PostController@upload');
Route::get  ('/api/post/getImage/{filename}',  'App\Http\Controllers\PostController@getImage'); 
Route::get  ('/api/post/category/{id}',  'App\Http\Controllers\PostController@getPostsByCategory'); 
Route::get  ('/api/post/user/{id}',  'App\Http\Controllers\PostController@getPostsByUser'); 


```
 
#### Crear RUTAS del API con MIDDLEWARE:

```php
Route::post ('/api/user/upload',  ['middleware' => 'api.auth'], 'App\Http\Controllers\UserController@upload');
Route::post ('/api/user/upload',  'App\Http\Controllers\UserController@upload')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);
```

#### Pasar PARÁMETROS a una ROUTE:

```php
Route::get('/pruebas/[nombre?]', function ($nombre? = null) { 
  return "nombre: ".$nombre; 
}
```

#### Listado de nuestras rutas disponibles (terminal):

> (Terminal) `php artisan route:list`
>
> (NOTA: El "**middleware**" es la página donde se ha creado, p.ej "web.php")


## UPLOAD desde POSTMAN (Leccion 11-31):

* Hacemos una petición **POST** a `http://localhost:8000/api/user/upload`.

* En **"Body" > "Form-data"** > Agregamos una KEY de tipo "file" que llamaremos "**file0**".

* En "Value", click en "select files" para explorar y seleccionar la imagen.

* Antes de hacer click en "SEND", confirmamos que en "Headers" tenemos la key "Authorization" con un token válido.

* Podemos luego ir a la carpeta **"storage/app/"** para confirmar que se ha creado la carpeta "**users**" y dentro contiene la imagen subida.


## Subida de archivos en Laravel:

En Laravel los archivos se suben a **"discos" (disk)** que son carpetas ubicadas en "**storage/app/**". Podremos crear tantas carpetas/disks como queramos dentro de esa ubicación.


#### REGISTRAR DISKS (CARPETAS):
Editamos **"config/filesystems.php"** el array "**disks**" y los agregamos (copiamos el "public"):

```php
'images' => [
  'driver' => 'local', 
   'root' => storage_path('app/images'),
   'url' => env('APP_URL').'/storage',
   'visibility' => 'public',
],
```

## Permitir acceso CORS (Leccion 14-48):

Abrimos **/public/index.php** y pegamos las siguientes cabeceras:

```php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
   die();
}
```

#### OPCIONAL 
(En caso de que NO funcionen las cabeceras de **"public/index.php"**:
Para evitar el error del CORS Access-Control-Allow-Origin al trabajar con AJAX, debemos configurar Apache para que comparta recursos.

En nuestro caso al trabajar en local, configuramos el fichero httpd.conf y le añado:

```php
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
</IfModule>
```

## Conectar BASE DE DATOS:  
Editar archivo **/.env**:

```php
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_rest_laravel
DB_USERNAME=root
DB_PASSWORD=null
```

## API-RESTful

Una API-REST procesa datos y devuelve un **JSON**:

```php
$data = array (
  'status' -> 'error',
  'code' -> 404,
  'message' -> 'not found.'
);
```

Ese array se retornará usando el método `json()` en la función `response()`:

```php
return response() -> json($data);
```

# Sentencias ORM
(Object Relational Mapping). Capa de abstracción de métodos que nos permite trabajar con la base de datos.

```php
// SELECT
$posts = Post::­all ();
$posts = Post::­fin­d (2);
$posts = Post::­whe­re (­'ti­tle', 'LIKE', '%et%'­) -> ­get ();
$posts = Post::­whe­re (­'ti­tle', 'LIKE', '%et%'­) ->­ tak­e (1­) -> ­ski­p (1­) -> ­get ();

// INSERT
$post = new Post;
$post-­>title = 'post1 title';
$post-­>body = 'post1 body';
$post-­>sa­ve ();

// Insert amb vector de dades
$data = array(
  'title' => 'post2 title',
  'body' => 'post2 body'
);
Post::­cre­ate­ ($d­ata);


// UPDATE
$post = Post::­fin­d (1);
$post-­>ti­tle­('u­pdated title');
$post-­>sa­ve ();


// DELETE
$post = Post::­fin­d (1);
$post-­>de­lete ();
```






