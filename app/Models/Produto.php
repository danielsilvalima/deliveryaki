<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Produto extends Model
{
  use HasFactory;

  //protected $fillable = ['id', 'uuid', 'descricao', 'status', 'vlr_unitario', 'empresa_id', 'categoria_id', 'created_at', 'updated_at', 'apresentacao'];
  protected $guarded = [];

  public static function booted()
  {
    static::creating(function ($model) {
      $model->uuid = Str::uuid();
    });
  }

  public function categoria()
  {
      return $this->belongsTo(Categoria::class);
  }

  public function empresa()
  {
      return $this->belongsTo(Empresa::class);
  }

  public function pedidos()
  {
      return $this->belongsToMany(Pedido::class, 'pedido_items');
  }
}
