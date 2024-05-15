<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pedido extends Model
{
  use HasFactory;

  protected $fillable = ['id', 'uuid', 'tipo_pagamento', 'tipo_entrega', 'vlr_taxa', 'vlr_total', 'delivered_at', 'deliver_at', 'cliente_id', 'empresa_id', 'status'];

  public static function booted()
  {
    static::creating(function ($model) {
      $model->uuid = Str::uuid();
    });
  }
}
