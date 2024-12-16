<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PedidoItem extends Model
{
  use HasFactory;

  //protected $fillable = ['id', 'uuid', 'qtd', 'vlr_unitario', 'vlr_total', 'pedido_id', 'produto_id', 'cliente_id', 'empresa_id'];
  protected $guarded = [];

  public static function booted()
  {
    static::creating(function ($model) {
      $model->uuid = Str::uuid();
    });
  }

  public function pedido()
  {
      return $this->belongsTo(Pedido::class);
  }

  public function produto()
  {
      return $this->belongsTo(Produto::class);
  }

  public function empresa()
  {
      return $this->belongsTo(Empresa::class);
  }

  public function cliente()
  {
      return $this->belongsTo(Cliente::class);
  }
}
