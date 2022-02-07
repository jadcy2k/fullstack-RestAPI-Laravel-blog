<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;

class TestORM_t15 extends Controller
{
    //Testear el ORM (para consultar a la BD):
    public function testOrm () {

      // Obtener todos los posts (usando el modelo):
      $posts = Post::all();
      
      // Recorrer y mostrar los posts:
      foreach ($posts as $post){
        echo "<h2>".$post -> title. "</h2>";
        echo "<p> By {$post -> user -> name} - {$post -> category -> name} </p>";
        echo "<p style='color:blue'>".$post -> title. "</p>";
        echo "<p>".$post -> content. "</p>";
        echo "<hr>";
      }

      echo "<hr><hr>";

      // Otra forma de listar a partir de categorías:
      $categories = Category::all();
      foreach ($categories as $category){

        echo "<h1>{$category -> name}</h1>";

        foreach ($category -> posts as $post){
          echo "<h2>".$post -> title. "</h2>";
          echo "<p> By {$post -> user -> name} </p>";
          echo "<p style='color:orange'>".$post -> title. "</p>";
          echo "<p>".$post -> content. "</p>";
          echo "<hr>";
        }
      }
    }
}
