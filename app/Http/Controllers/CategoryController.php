<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Leccion 13--35,36,37,38:
     * ========================
     * IMPORTANTE: Si en las RUTAS usamos:
     *   Route::resource('/api/category', 'App\Http\Controllers\CategoryController');
     * Podremos ver que automaticamente nos proponen nuevas rutas.
     * 
     * (TERMINAL) php artisan route:list
     * +-----------+------------------------------+------------------+-------------------------------------------------------+
     * | Method    | URI                          | Name             | Action  
     * +-----------+------------------------------+------------------+-------------------------------------------------------+    
     * | GET|HEAD  | api/category                 | category.index   | App\Http\Controllers\CategoryController@index 
     * | POST      | api/category                 | category.store   | App\Http\Controllers\CategoryController@store 
     * | GET|HEAD  | api/category/create          | category.create  | App\Http\Controllers\CategoryController@create 
     * | GET|HEAD  | api/category/{category}      | category.show    | App\Http\Controllers\CategoryController@show 
     * | PUT|PATCH | api/category/{category}      | category.update  | App\Http\Controllers\CategoryController@update
     * | DELETE    | api/category/{category}      | category.destroy | App\Http\Controllers\CategoryController@destroy
     * | GET|HEAD  | api/category/{category}/edit | category.edit    | App\Http\Controllers\CategoryController@edit
     * +-----------+------------------------------+------------------+-------------------------------------------------------+
     * 
     * Son las diferentes propuestas de ruta por defecto para esta API.
     * Por tanto, podríamos definiremos las funciones:
     *    "index", "store", "create", "show", "update", "destroy" o "edit" (según necesitemos).
     * - No son necesarias todas, es sólo una guía de cómo funcionaría la API.
     * - No hace falta que las definamos en las ruta (web.php), pues van implícitas en "Route::resource".
     * */ 



    // "INDEX" equivale a un GET de todas las categorías:
    // ------------------------------------------------------------
    public function index () {
      $categories = Category::all();

      return response() -> json ([
        'code' => 200,
        'status' => 'success',
        'categories' => $categories,
      ]);      
    }

// #######################################################################   
// #######################################################################  

    // "SHOW" es un GET de una categoría concreta: 
    // ------------------------------------------------------------
    public function show ($id) {
      $category = Category::find($id);

      if (is_object($category)){
        $data = [
          'code' => 200,
          'status' => 'success',
          'category' => $category,
        ];
      } else {
        $data = [
          'code' => 400,
          'status' => 'error',
          'message' => 'CATEGORIA NO ENCONTRADA.',
        ];
      }
      return response() -> json($data, $data['code']);
    }

// #######################################################################   
// #######################################################################  

    // "STORE" requiere autenticación!!!
    // ------------------------------------------------------------
    /**
     * IMPORTANTE: Este método requiere autenticación. 
     * Podríamos usar el middleware (api.auth) cuando creamos el Route::POST, 
     * o bien podremos definir una función __CONSTRUCT() donde indicamos:
     *    - Qué middleware queremos usar.
     *    - Si queremos usarlo en todos los métodos o hay alguna excepción. 
     *
     * Por ejemplo: 
     *  
     *   public function __construct() {
     *     $this -> middleware('api.auth', ['except' => ['index', 'show']]);
     *   }
     * 
     * Nos indica que podemos usar el middleware EXCEPTO en 'index' y 'show': 
     * 
     */
    
    public function __construct() {
      $this -> middleware('api.auth', ['except' => ['index', 'show']]);      
    }
    
    // "STORE" es un POST que recibe datos y los guarda 
    // ------------------------------------------------------------
    public function store(Request $request) {
      # Recoger datos:
      $json = $request -> input ('json', null);
      $params_array = json_decode($json, true);

      if (!empty($params_array)){

        # Validar datos:
        # Se usa el paquete "Illuminate\Support\Facades\Validator" de Laravel, 
        $validate = Validator::make($params_array, [
          'name' => 'required'
        ]);

        # Guardar la categoría en BD:
        if ($validate->fails()) {
          $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'CATEGORIA INVÁLIDA.',
          ];
        } else {
          $category = new Category();
          $category -> name = $params_array['name'];
          $category -> save();
          
          $data = [
            'code' => 200,
            'status' => 'success',
            'category' => $category,
          ];
        }

      } else {
        $data = [
          'code' => 400,
          'status' => 'error',
          'message' => 'DATOS DE CATEGORIA INCORRECTOS.',
        ];
      }
      return response() -> json($data, $data['code']);
    }

// #######################################################################   
// #######################################################################  

    // "UPDATE" es un PUT que actualiza un registro concreto (id):
    // -------------------------------------------------------------
    public function update ($id, Request $request) {
      # Recoger datos:
      $json = $request -> input ('json', null);
      $params_array = json_decode($json, true);

      if (!empty($params_array)){

        # Validar datos:
        $validate = Validator::make($params_array, [
          'name' => 'required'
        ]);

        # Quitar campos que NO queremos actualizar:
        unset($params_array['id']);
        unset($params_array['created_at']);

        # Actualizar registro:
        if ($validate->fails()) {
          $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'CATEGORIA INVÁLIDA.',
          ];
        } else {
          $category = Category::where('id', $id) -> update($params_array);
          $data = [
            'code' => 200,
            'status' => 'success',
            'category' => $params_array,
          ];
        }

      } else {
        $data = [
          'code' => 400,
          'status' => 'error',
          'message' => 'DATOS DE CATEGORIA VACIOS.',
        ];
      }
      return response() -> json($data, $data['code']);
    }

// #######################################################################   
// #######################################################################  
    
}
