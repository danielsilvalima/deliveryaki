<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaVisibilidade extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function categoria()
  {
    return $this->belongsTo(Categoria::class);
  }

  public function horarioExpediente()
  {
    return $this->belongsTo(HorarioExpediente::class, 'horario_expediente_id');
  }
}
