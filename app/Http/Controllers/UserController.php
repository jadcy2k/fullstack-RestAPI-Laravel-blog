<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    // ACCIONES DEL TUTORIAL-API:
    // #######################################################################   
    // #######################################################################   
    public function register (Request $request) {

      // 1) Recoger datos JSON de "Postman" por POST:
      // ====================================================================
      // Será la variable 'json' o null.
      // que es una "KEY" enviada como X-WWW-FORM sólo para testear:
      $json = $request -> input('json', null);
      $params_objeto = json_decode($json); // Devuelve un OBJETO.
      $params_array = json_decode($json, true); // Devuelve un ARRAY (se coloca "true").

      // Es interesante testear la entrada:
      //var_dump($params_array); die();


      // 2) VALIDAR DATOS:
      // ====================================================================
      // Se usa el paquete "Illuminate\Support\Facades\Validator" de Laravel, 
      // el cual proporciona reglas de validación.
      // Documentación: https://styde.net/laravel-6-doc-validacion/
      
      // Si el array de parámetros NO está vacío...
      if (!empty($params_array)){

        // Limpiar espacios en blanco:
        $params_array = array_map('trim', $params_array);

        // Crear reglas de validación:
        // NOTA "unique:users" indica que ese campo será UNICO enla tabla BD "users".
        $validate = Validator::make($params_array, [
          'name'      => 'required | alpha',
          'surname'   => 'required | alpha',
          'email'     => 'required | email | unique:users',
          'password'  => 'required',
        ]);
  
        // Checkear si hay errores:
        if ($validate->fails()) {
          $data = array(
              'status' => 'error',
              'code' => 404,
              'message' => 'Error al crear usuario.',
              'errors' => $validate->errors()
          );

        // Si NO hay errores:  
        } else {

          // Cifrar la contraseña con "password_hash" y método PASSWORD_BCRYPT:
          // NOTA (leccion 10-25): En lugar de este método, usaremos el "hash",
          // ya que este genera cada vez un password  nuevo y no se puede comparar
          // con el de la BD:
          //  $pwd = password_hash($params_array['password'], PASSWORD_BCRYPT, ['cost' => 4]);
          $pwd = hash('sha256', $params_array['password']);

          // Crear nuevo usuario:
          // Para ello, cargamos el MODELO "USER" 
          // Normalmente viene por defecto: "use App\Models\User";
          // ----------------------------------
          $user = new User();
          $user -> name = $params_array['name'];
          $user -> surname = $params_array['surname'];
          $user -> email = $params_array['email'];
          $user -> password = $pwd;
          $user -> role = 'ROLE_USER';

          // Guardar el usuario en la BD:
          $user -> save();

          // Devolver respuesta:
          $data = array(
              'status' => 'success',
              'code' => 200,
              'message' => 'El usuario se ha creado correctamente',
              'user' => $user,
          );            
        }

      // Si el array de parámetros está vacío:
      } else {
        $data = array(
          'status' => 'error',
          'code' => 404,
          'message' => 'DATOS INCORRECTOS.',
        );
      }
      // Fin de validar datos.
      // ====================================================================
      
      return response()->json($data, $data['code']);
    }


 // #######################################################################   
 // #######################################################################   
 // #######################################################################  

    public function login (Request $request) {
      
      // 0) Cargamos el HELPER "JwtAuth" (creado en leccion 10-24):
      $JwtAuth = new \App\Helpers\JwtAuth();

      // 1) Recibimos datos por POSTMAN: http://localhost:8000/api/login
      // ====================================================================
      // será una KEY 'json' enviada por X-WWW-FORM (como prueba)
      // En el futuro esos datos se enviarán mediante un formulario frontend.
      $json = $request -> input('json', null);
      var_dump($json);
      $params = json_decode($json);
      $params_objeto = json_decode($json); // Devuelve un OBJETO.
      $params_array = json_decode($json, true); // Devuelve un ARRAY (se coloca "true").


      // 2) Validar los datos recibidos (usamos VALIDATOR como en la función "register)
      // ====================================================================
      $validate = Validator::make($params_array, [
        'email'     => 'required | email',
        'password'  => 'required',
      ]);

      // Checkear si hay errores:
      if ($validate->fails()) {
        $signup = array(
            'status' => 'error',
            'code' => 404,
            'message' => 'Error de identificación de usuario.',
            'errors' => $validate->errors()
        );

      // Si NO hay errores:  
      } else {
        // 3) Cifrar password (con método "hash" sha256)
        $pwd = hash('sha256', $params -> password);  

        // 4) Devolver el token:
        $signup = $JwtAuth -> signup($params -> email, $pwd);

        // 5) O bien, devolver el usuario (si no nos pasan el parámetro "getToken"):
        if (!empty($params -> getToken)) {          
          $signup = $JwtAuth -> signup($params -> email, $pwd, true);
        }
      }

      //NOTA: Si queremos retornar el objeto de usuario completo:
      return response()->json($signup, 200);
      // ====================================================================
    }

 // #######################################################################   
 // #######################################################################   
 // #######################################################################  

    public function update(Request $request) {

      // 1) Comprobar si el usuario está identificado:
      // ====================================================================
      // NOTA (leccion 10-30): Esta funcionalidad la copiaremos en el Middleware "ApiAuthMiddleware.php":
      $token = $request -> header ('Authorization');
      $jwtAuth = new \App\Helpers\JwtAuth();
      $checkToken = $jwtAuth -> checkToken($token);
      var_dump($checkToken);

      // Recoger datos de entrada JSON:
      //$json = $request -> input('json', null);
      $json = '{"name":"Pepe", "surname":"Ruiz", "email":"pepe@pepe.com", "password":"pepe"}';
      $params_array = json_decode($json, true);

      //var_dump($params_array); die();

      if ($checkToken && !empty($params_array)) {
        // 2) Actualizar usuario si está identificado:
        // ==================================================================== 

        // Obtener usuario identificado:
        $user = $jwtAuth -> checkToken($token, true);

        // Validar datos:
        $validate = Validator::make($params_array, [
          'name'      => 'required | alpha',
          'surname'   => 'required | alpha',
          // NOTA: a "unique" podemos pasarle el ID del usuario que queremos que ignore (id = sub en JWT)
          // de forma que validará el email y en caso de actualizar el usuario NO dará error:
          'email'     => 'required | email | unique:users,email,'.$user -> sub
          // NOTA: Si la linea de validación del email NO funciona, se puede crear
          // un array con las reglas de validación:
          // 'email' => [
          //   'required',
          //   'email',
          //   Rule::unique('users')->ignore($user->sub)
          // ]
        ]);

        if ($validate->fails()) {
          $data = array(
              'status' => 'error',
              'code' => 404,
              'message' => 'ERROR DE VALIDACIÓN.',
              'errors' => $validate->errors()
          );
          return response() -> json ($data, $data['code']);
          die();
        }

        // Quitar campos que No hay que actualizar:
        unset($params_array['id']);
        unset($params_array['role']);
        unset($params_array['password']);
        unset($params_array['created_at']);
        unset($params_array['remember_token']);

        // Actualizar usuario en BD:
        $user_update = User::where ('id', $user -> sub) -> update ($params_array);

        // Devolver mensaje exito:
        $data = array(
          'status' => 'success',
          'code' => 200,
          'message' => 'USUARIO ACTUALIZADO OK.',
          'user' => $user_update,
          'changes' => $params_array,
        );


      } else {
        // Devolver error:
        $data = array(
          'status' => 'error',
          'code' => 404,
          'message' => 'USUARIO NO IDENTIFICADO.',
        );
      }      
      
      return response() -> json ($data, $data['code']);
    }

 // #######################################################################   
 // #######################################################################   
 // #######################################################################  

  public function upload (Request $request) { // Lecciones 10-29 y 10-31.
    // NOTA: La autenticación del usuario se realiza en el middleware 'ApiAuthMiddleware.php'. 
    
    // Por tanto, lo que se ejecute dentro de esta función será gracias a que el middleware 
    // ha dado por bueno la validación del usuario.

    // UPLOAD AVATAR DE USUARIO:
    // -------------------------
    //1) Recoger datos de la petición:
    // NOTA: lo hacemos mediante request pero a través de la función "file" pasándole "file0"
    // (es el nombre que usa por defecto la librería de subida de archivos):

    $image = $request -> file('file0');

    // 2) Validar el archivo (Leccion:10-32):
    // La regla de validación está basada en "mime-type":
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
 // ####################################################################### 

  public function getImage ($filename) { // Leccion:10-33 

    if ($filename){
      // Comprobar si la imagen existe:
      // -------------------------------
      $isset = Storage::disk('users') -> exists ($filename);

      if ($isset) {
      // Enviar la imagen:
      // -----------------
        $file = Storage::disk('users') ->  get($filename);

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
  }
 
  // #######################################################################   
 // #######################################################################   
 // ####################################################################### 
  
  public function getUserById ($id) { // Leccion:10-34 
    // En la lección lo llaman "detail".

    // El Modelo User permite el uso de "FIND" (equivale a SELECT SQL):
    $user = User::find ($id); //devuelve un OBJETO.

    if (is_object($user)){

      //Devolver los datos de ese objeto:
      $data = array (
        'code' => 200,
        'status' => 'success',
        'user' => $user,
      );

    } else {

      $data = array (
        'code' => 400,
        'status' => 'error',
        'message' => 'USUARIO NO EXISTE'
      );

    }
    return response() -> json($data);
  }
 
 // #######################################################################   
 // #######################################################################   
 // #######################################################################   
}
