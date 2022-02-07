<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    // Relación de uno a muchos inversa:
    public function user(){
      return $this -> belongsTo('App\Models\User', 'user_id');
    }

    public function category(){
      return $this -> belongsTo('App\Models\Category', 'category_id');
    }

    # (Leccion 14-44): El modelo Post deberá tener el listado de campos editables
    # Igual que tiene "User":
    protected $fillable = [
      'title',
      'content',
      'category_id',
    ];
}
