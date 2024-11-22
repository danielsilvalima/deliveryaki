<?php
namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class HashGenerator
{
    /**
     * Gera um hash único de 8 caracteres.
     *
     * @param Model $model
     * @param string $column
     * @return string
     */
    public static function generateUniqueHash8Caracter(): string
    {
      $hash = Str::random(8);

      return $hash;
    }
}
