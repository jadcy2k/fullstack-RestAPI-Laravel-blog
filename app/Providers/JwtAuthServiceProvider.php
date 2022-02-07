<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class JwtAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     * LA DEFINICIÓN VIENE CREADA POR DEFECTO.
     * @return void
     */
    public function register()
    {
        // AGREGAMOS NUESTRO CÓDIGO:
        require_once app_path()."/Helpers/JwtAuth.php";
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
