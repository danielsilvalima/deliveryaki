<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fatura extends Model
{
  use HasFactory;

  protected $guarded = [];

  /*public function empresa()
  {
    return $this->morphTo(null, 'tipo_app', 'empresa_id');
  }*/

  // Alternativamente, você pode fazer relacionamentos manuais
  public function empresaDelivery()
  {
    return $this->belongsTo(\App\Models\Empresa::class, 'empresa_id');
  }

  public function empresaAgenda()
  {
    return $this->belongsTo(\App\Models\AgendaEmpresa::class, 'empresa_id');
  }
}
