<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PedidoItem extends Model
{
  use HasFactory;

  protected $fillable = ['id', 'uuid', 'qtd', 'vlr_unitario', 'vlr_total', 'pedido_id', 'produto_id', 'cliente_id', 'empresa_id'];

  public static function booted()
  {
    static::creating(function ($model) {
      $model->uuid = Str::uuid();
    });
  }
}
