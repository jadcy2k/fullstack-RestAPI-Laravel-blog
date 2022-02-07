<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next) {
      // Comprobar token de usuario:
      // ====================================================================
      $token = $request -> header ('Authorization');
      $jwtAuth = new \App\Helpers\JwtAuth();
      $checkToken = $jwtAuth -> checkToken($token);

      // Si el token es correcto, seguimos con la siguiente acción mediante "$next".
      // en el ejemplo "Leccion:10-30" será la función "upload" del UserController o de la que se haya
      // definido en "Web.php" (ROUTES).
      if ($checkToken) {
        return $next($request);
      } else {
        $data = array(
          'status' => 'error',
          'code' => 404,
          'message' => 'USUARIO NO IDENTIFICADO.',
        );
        return response() -> json ($data, $data['code']);
      }
      // ====================================================================
    }
}
