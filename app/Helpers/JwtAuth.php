<?php

/**
 * Librería de AYUDA para implementar el uso de JWT (JSON web tokens)
 * https://www.udemy.com/course/master-en-desarrollo-web-full-stack-angular-node-laravel-symfony/learn/lecture/13144882#notes
 * Visto en la lección 10-24.
 */

 // Leer aacerca de "namespaces: https://diego.com.es/namespaces-en-php
  namespace App\Helpers;

 // Librerías que vamos a utilizar: 
 use Firebase\JWT\JWT;
 use Illuminate\Support\Facades\DB;
 use App\Models\User;

 
 // Creación de la clase que será instanciada para su uso (considerando el "namespace"):
 class JwtAuth {
   public $key;

   
  
   // Constructor (definirá la KEY)
   // ====================================================================
   public function __construct() {
     $this -> key = 'esto_es_una_clave_de_muestra_1234_#?!/0';     
   }

   // Signup: Efectua el login creando un TOKEN:
   // ====================================================================
   public function signup($email, $password, $getToken = null){
      // Consultar en la BD si existe el usuario: (Leccion 10-25)
      $user = User::where([
        'email'     => $email,
        'password'  => $password
      ]) -> first(); //Devuelve un OBJETO.

      $signup = false;
      is_object($user) && $signup = true;

      // Generar TOKEN si existe el usuario:
      if ($signup){
        $duration = (7*24*60*60); //Duración del token en SEGUNDOS. P.ej: 7 días.

        // Definir parámetros del token:
        $token = array (
          'sub'       =>  $user->id,
          'email'     =>  $user->email,
          'name'      =>  $user->name,
          'surname'   =>  $user->surname,
          'iat'       =>  time(), // Parmámetro de JWT (fecha de creación)
          'exp'       =>  time() + $duration, // Parám. JWT (expired date)
        );

        // Crear token con JWT usando el algoritmo de encriptación HS256:
        $jwt = JWT::encode($token, $this->key, 'HS256');
        $decoded = JWT::decode($jwt, $this -> key, ['HS256']);

        // En caso de que no haya token inicial, se devuelve el nuevo.
        // Si no, se devuelve el actual decodificado:
        
        if (is_null($getToken)){
          $data = $jwt;
        } else {
          $data = $decoded;
        }
        
      // Si hay un error al logarse:
      } else {
        $data = array (
          'status'    => 'error',
          'message'    => 'login incorrecto.',
        );
      }

      return $data;
   }


// #######################################################################
// #######################################################################
// #######################################################################


   // Checkear el TOKEN y devolver el usuario: (leccion 10-27)
   // ====================================================================
   public function checkToken ($jwt, $getIdentity = false){
     
    // $auth será el parámetro a devolver (booleano)
    $auth = false;
         
    // Vamos a checkear el token usando un "try-catch" para controlar los posibles errores.
    // Las excepciones típicas en este proceso pueden ser: "UnexpectedValueException" y "DomainException":
     
    try {
      $jwt = str_replace('"', '', $jwt); // Quitamos las comillas para evitar errores.
      $decoded = JWT::decode($jwt, $this->key, ['HS256']); // devuelve un OBJETO.
    } catch (\UnexpectedValueException $e) {
       $auth = false;
    } catch (\DomainException $e) {
      $auth = false;
    }

    // Ahora viene la validación:
    // Si existe el objeto, no está vacío y contiene "sub", entonces se autentifica (auth= true)
    if ( !empty($decoded) && is_object($decoded)  && isset($decoded->sub)) {
      $auth = true;
    } else {
      $auth = false;
    }

    // En caso que nos pasen el parámetro "getIdentity", devolveremos el objeto "$decoded":
    if ($getIdentity) {
      return $decoded;
    }

    return $auth;   
   }

   



 }
