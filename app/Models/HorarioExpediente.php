<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioExpediente extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function empresa_expedientes()
  {
    return $this->hasMany(EmpresaExpediente::class, 'horario_expediente_id');
  }

  public function categoriasVisiveis()
  {
    return $this->hasMany(CategoriaVisibilidade::class, 'horario_expediente_id');
  }
}
