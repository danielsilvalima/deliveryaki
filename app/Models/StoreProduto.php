<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreProduto extends Model
{
  use HasFactory;

  protected $table = 'produto';
  protected $guarded = [];
  protected $connection = 'mysql2';
}
