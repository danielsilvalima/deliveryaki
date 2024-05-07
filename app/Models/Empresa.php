<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Empresa extends Model
{
  use HasFactory;

  protected $fillable = ['id', 'uuid', 'cnpj', 'razao_social', 'telefone', 'celular', 'status'];

  public static function booted()
  {
    static::creating(function ($model) {
      $model->uuid = Str::uuid();
    });
  }
}
