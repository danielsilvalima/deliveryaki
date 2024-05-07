<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cliente extends Model
{
  use HasFactory;

  protected $fillable = ['id', 'uuid', 'nome_completo', 'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'celular', 'status', 'empresa_id'];

  public static function booted()
  {
    static::creating(function ($model) {
      $model->uuid = Str::uuid();
    });
  }
}
