<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    // (Leccion 08-16) Para testear esto:
    // https://www.udemy.com/course/master-en-desarrollo-web-full-stack-angular-node-laravel-symfony/learn/lecture/13139944#notes
    // Le pasamos "Request" que será lo que utilicemos para enviar y recibir cosas:    
    public function test (Request $request) { return "Acción de prueba de POST-CONTROLLER"; }

    /**
     * Leccion 14-39
     * =======================
     * Igual que en CategoryController.php, definiremos las rutas como "resource":
     *   Route::resource('/api/post', 'App\Http\Controllers\PostController');
     * Esto nos propone la siguiente API:
     * 
     * (TERMINAL) php artisan route:list
     * +-----------+------------------------------+------------------+-------------------------------------------------------+
     * | Method    | URI                          | Name             | Action  
     * +-----------+------------------------------+------------------+-------------------------------------------------------+  
     * | GET|HEAD  | api/post                     | post.index       | App\Http\Controllers\PostController@index
     * | POST      | api/post                     | post.store       | App\Http\Controllers\PostController@store
     * | GET|HEAD  | api/post/create              | post.create      | App\Http\Controllers\PostController@create
     * | GET|HEAD  | api/post/{post}              | post.show        | App\Http\Controllers\PostController@show
     * | PUT|PATCH | api/post/{post}              | post.update      | App\Http\Controllers\PostController@update
     * | DELETE    | api/post/{post}              | post.destroy     | App\Http\Controllers\PostController@destroy
     * | GET|HEAD  | api/post/{post}/edit         | post.edit        | App\Http\Controllers\PostController@edit 
     * +-----------+------------------------------+------------------+-------------------------------------------------------+ 
     * 
     * Son las diferentes propuestas de ruta por defecto para esta API.
     * Por tanto, podríamos definiremos las funciones:
     *    "index", "store", "create", "show", "update", "destroy" o "edit" (según necesitemos).
     * - No son necesarias todas, es sólo una guía de cómo funcionaría la API.
     * - No hace falta que las definamos en las ruta (web.php), pues van implícitas en "Route::resource".
     * */ 




    // USO DE "MIDDLEWARE" SI SE NECESITA AUTENTICACIÓN:
    // ------------------------------------------------------------
    /**
     * Podríamos usar el middleware (api.auth) cuando creamos el Route::POST, 
     * o bien podremos definir una función __CONSTRUCT() donde indicamos:
     * 
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
      $this -> middleware('api.auth', 
        ['except' => 
          [
            'index', 
            'show',
            'getImage',
            'getPostsByCategory',
            'getPostsByUser'
          ]
        ]);      
    }
    
// #######################################################################   
// ####################################################################### 

    // "INDEX" equivale a un GET de todos los posts:
    // ------------------------------------------------------------
    public function index () { // Leccion 14-39
      $posts = Post::all();

      return response() -> json ([
        'code' => 200,
        'status' => 'success',
        'posts' => $posts,
      ]);      
    }

// #######################################################################   
// ####################################################################### 

    // "SHOW" es un GET de un post concreto: 
    // ------------------------------------------------------------
    public function show ($id) { // Leccion 14-39
      //obtenemos el post según la ID y además nos devolverá su categoría entera relacionada:
      $post = Post::find($id) -> load('category');

      if (is_object($post)){
        $data = [
          'code' => 200,
          'status' => 'success',
          'post' => $post,
        ];
      } else {
        $data = [
          'code' => 400,
          'status' => 'error',
          'message' => 'POST NO ENCONTRADO.',
        ];
      }
      return response() -> json($data, $data['code']);
    }    

// #######################################################################   
// ####################################################################### 

    // GetIdentity: Nos servirá para obtener el usuario autenticado: 
    // ------------------------------------------------------------
    private function getIdentity($request) { // Leccion 14-44
      # Cargamos el HELPER "JwtAuth" (creado en leccion 10-24):
      $jwtAuth = new \App\Helpers\JwtAuth();
      $token = $request -> header ('Authorization', null);        
      $user = $jwtAuth -> checkToken ($token, true);
      return $user;
    }

// #######################################################################   
// ####################################################################### 

    // "STORE" es un POST que recibe datos y los guarda 
    // ------------------------------------------------------------
    public function store(Request $request) { // Leccion 14-40
      # Recoger datos:
      $json = $request -> input ('json', null);
      $params_array = json_decode($json, true);

      if (!empty($params_array)){

        # Obtener usuario:
        $user = $this -> getIdentity($request);

        # Validar datos:
        # Se usa el paquete "Illuminate\Support\Facades\Validator" de Laravel, 
        $validate = Validator::make($params_array, [
          'title' => 'required',
          'content' => 'required',
          'category_id' => 'required',
          'image' => 'required',
        ]);

        # Guardar la categoría en BD:
        if ($validate->fails()) {
          $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'CATEGORIA INVÁLIDA.',
          ];
        } else {
          $post = new Post();
          $post -> user_id = $user -> sub;
          $post -> title = $params_array['title'];
          $post -> content = $params_array['content'];
          $post -> category_id = $params_array['category_id'];
          $post -> image = $params_array['image'];
          $post -> save();
          
          $data = [
            'code' => 200,
            'status' => 'success',
            'post' => $post,
          ];
        }

      } else {
        $data = [
          'code' => 400,
          'status' => 'error',
          'message' => 'DATOS DEL POST INCORRECTOS.',
        ];
      }
      return response() -> json($data, $data['code']);
    }

// #######################################################################   
// ####################################################################### 

    // "UPDATE" es un PUT que actualiza un registro concreto (id):
    // -------------------------------------------------------------
    public function update ($id, Request $request) { // Leccion 14-42

      # Obtener usuario:
      $user = $this -> getIdentity($request);

      # Recoger datos:
      $json = $request -> input ('json', null);
      $params_array = json_decode($json, true);

      if (!empty($params_array)){

        # Validar datos:
        $validate = Validator::make($params_array, [
          'title' => 'required',
          'content' => 'required',
          'category_id' => 'required',
          'image' => 'required',
        ]);

        # Quitar campos que NO queremos actualizar:
        unset($params_array['id']);
        unset($params_array['user_id']);
        unset($params_array['created_at']);

        # Actualizar registro:
        if ($validate->fails()) {
          $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'POST INVÁLIDO.',
          ];
        } else {
          # (Leccion 14-42):
          # Si en lugar de "update" usamos "updateOrCreate", será posible obtener el "$post" actualizado.
          # De la otra forma no nos deja acceder a él (sólo nos da su ID):
          
           $post = Post::where('id', $id)
                      -> where ('user_id', $user -> sub)
                      -> updateOrCreate($params_array);
          
          # IMPORTANTE: Conformar que los campos están definidos en "fillable" del modelo "Post".

          # NOTA: Si se van a concatenar muchos "where" (condiciones) los podremos meter en un array:
          /**
           * $where ['id' => $id, 'user_id' => $user->sub];     
           *   ...y luego pasarlo al "updateOrCreate" como parámetro:
           * $post = Post::updateOrCreate ($where, $params_array);
           * 
           */
                        
          $data = [
            'code' => 200,
            'status' => 'success',
            'changes' => $params_array,
            'post' => $post, // Ahora sí que nos devolvería el objeto completo y no solo su ID.
          ];
        }

      } else {
        $data = [
          'code' => 400,
          'status' => 'error',
          'message' => 'DATOS DE POST INCORRECTOS.',
        ];
      }
      return response() -> json($data, $data['code']);
    }

// #######################################################################   
// #######################################################################  

    // "DESTROY" es un DELETE que elimina un registro concreto (id):
    // -------------------------------------------------------------
    public function destroy ($id, Request $request) { // Leccion 14-43

      # Obtener usuario identificado:
      $user = $this -> getIdentity($request);      
      
      # Obtener el post:
      //De forma normal (Leccion 14-43):
          //$post = Post::find($id);
      //Forzando a que corresponda al $user identificado (Leccion 14-44):
      $post = Post::where('id', $id) 
                    -> where ('user_id', $user -> sub)
                    -> first ();
      

      # Si existe, eliminarlo:
      if (!empty($post)) {
        $post -> delete();

        $data = [
          'code' => 200,
          'status' => 'success',
          'post' => $post,
        ];

      } else {

        $data = [
          'code' => 400,
          'status' => 'error',
          'message' => 'EL POST NO EXISTE.',
        ];

      }

      return response() -> json($data, $data['code']);
    }
// #######################################################################   
// #######################################################################  
    
    // UPLOAD de imagenes, GET (Mirar descripcion en "UserController")
    public function upload (Request $request) { // Leccion 14-45
      # Obtener imagen:
      $image = $request -> file ('file0');

      # Validar imagen:
      $validate = Validator::make($request -> all(), [
        'file0' => 'required | image | mimes:jpg,jpeg,png,gif'
      ]);
    
      if (!$image || $validate->fails()){
  
        $data = array (
          'code' => 400,
          'status' => 'error',
          'message' => 'ERROR AL SUBIR IMAGEN.'
        );     
  
      } else {
  
        // 3) Subir el archivo (Leccion:10-31) al disco "users" con el método PUT:
        $image_name = time() . $image -> getClientOriginalName();
        Storage::disk('users') -> put ($image_name, File::get($image));
  
        $data = array (
          'code' => 200,
          'status' => 'success',
          'image' => $image_name,
        );
        
      }         
      //return response($data) -> header('Content-Type', 'text/plain');
      return response() -> json($data);
    }

// #######################################################################  
// #######################################################################  

    // getImage, GET (igual que en UserController):
    // ----------------------------------------------------
    public function getImage ($filename) { // Leccion 14-46 
      # Comprobar si existe el fichero
      $isset = Storage::disk ('users') -> exists ($filename);

      # Obtener imagen
      if ($isset) {
        $file = Storage::disk ('users') -> get ($filename);

      // envío en crudo:
      //return new Response($file, 200); 
      // ...o bien envío como archivo (comentar lo anterior):
      return response($file) -> header('Content-Type', 'image/jpeg');
      
      } else {
        $data = array (
          'code' => 400,
          'status' => 'error',
          'message' => 'LA IMAGEN NO EXISTE'
        );
        return response() -> json($data);
      }
    }

// #######################################################################  
// #######################################################################  

    public function getPostsByCategory ($id) {
      $posts = Post::where ('category_id', $id) -> get();

      $data = array (
        'code' => 200,
        'status' => 'success',
        'posts' => $posts,
      );
      return response() -> json($data);
    }

// #######################################################################  
// #######################################################################  

    public function getPostsByUser ($id) {
      $posts = Post::where ('user_id', $id) -> get();

      $data = array (
        'code' => 200,
        'status' => 'success',
        'posts' => $posts,
      );
      return response() -> json($data);
    }

// #######################################################################  
// ####################################################################### 

}
