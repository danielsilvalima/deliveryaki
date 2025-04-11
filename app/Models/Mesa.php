<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function empresas()
  {
    return $this->hasMany(Empresa::class, 'mesa_id');
  }

  public function pedidos()
  {
    return $this->hasMany(Pedido::class, 'mesa_id');
  }
}
