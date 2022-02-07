<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| RUTAS DEL TUTORIAL API
|--------------------------------------------------------------------------
| GET: Conseguir datos o recursos.
| GET: Guardar datos o recursos, lógica desde formularios.
| PUT: Actualizar datos o recursos.
| DELETE: Eliminar datos o recursos.
*/

// Sólo para hacer prueba:
Route::get ('/entrada/test',  'App\Http\Controllers\PostController@test');
Route::get ('/test/posts',    'App\Http\Controllers\TestORM_t15@testOrm');


// USUARIO:
Route::get  ('/user/register',    'App\Http\Controllers\UserController@register');
Route::get  ('/user/login',       'App\Http\Controllers\UserController@login');
Route::post ('/api/register',     'App\Http\Controllers\UserController@register');
Route::post ('/api/login',        'App\Http\Controllers\UserController@login');
Route::put  ('/api/user/update',  'App\Http\Controllers\UserController@update'); //PUT para "updates"
// USUARIO > UPLOAD => Con Middleware (hay 2 formas de definirlo. Funciona la 2a):
Route::post ('/api/user/upload',  ['middleware' => 'api.auth'], 'App\Http\Controllers\UserController@upload');
Route::post ('/api/user/upload',  'App\Http\Controllers\UserController@upload')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);
// AVATAR:
Route::get  ('/api/user/avatar/{filename}',  'App\Http\Controllers\UserController@getImage'); 
// DETALLES USER:
Route::get  ('/api/user/getUserById/{id}',  'App\Http\Controllers\UserController@getUserById');

// CATEGORÍAS:
Route::resource('/api/category', 'App\Http\Controllers\CategoryController');
//Route::post ('/api/category/store',  'App\Http\ControllersCategoryController@store')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);

// POSTS:
Route::resource('/api/post', 'App\Http\Controllers\PostController');
Route::post ('/api/post/upload', 'App\Http\Controllers\PostController@upload'); // No hace falta "middleware" porque el controlador ya está protegido.
Route::get  ('/api/post/getImage/{filename}',  'App\Http\Controllers\PostController@getImage'); 
Route::get  ('/api/post/category/{id}',  'App\Http\Controllers\PostController@getPostsByCategory'); 
Route::get  ('/api/post/user/{id}',  'App\Http\Controllers\PostController@getPostsByUser'); 

