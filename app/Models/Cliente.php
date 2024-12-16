<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cliente extends Model
{
  use HasFactory;

  //protected $fillable = ['id', 'uuid', 'nome_completo', 'cep', 'numero', 'celular', 'status', 'empresa_id', 'cep_id'];
  protected $guarded = [];

  public static function booted()
  {
    static::creating(function ($model) {
      $model->uuid = Str::uuid();
    });
  }

  public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function pedido_items()
    {
        return $this->hasMany(PedidoItem::class);
    }
}
