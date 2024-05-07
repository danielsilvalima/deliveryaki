<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Produto extends Model
{
  use HasFactory;

  protected $fillable = ['id', 'uuid', 'descricao', 'status', 'empresa_id', 'categoria_id', 'created_at', 'updated_at'];

  public static function booted()
  {
    static::creating(function ($model) {
      $model->uuid = Str::uuid();
    });
  }
}
