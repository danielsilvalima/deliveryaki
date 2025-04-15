<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pedido extends Model
{
  use HasFactory;

  //protected $fillable = ['id', 'uuid', 'tipo_pagamento', 'tipo_entrega', 'vlr_taxa', 'vlr_total', 'delivered_at', 'deliver_at', 'cliente_id', 'empresa_id', 'status'];
  protected $guarded = [];

  public static function booted()
  {
    static::creating(function ($model) {
      $model->uuid = Str::uuid();
    });
  }

  public function cliente()
  {
    return $this->belongsTo(Cliente::class);
  }

  public function empresa()
  {
    return $this->belongsTo(Empresa::class);
  }

  public function pedido_items()
  {
    return $this->hasMany(PedidoItem::class);
  }

  public function pedido_notificacaos()
  {
    return $this->hasMany(PedidoNotificacao::class, 'pedido_id');
  }

  public function mesa()
  {
    return $this->belongsTo(Mesa::class);
  }
}
