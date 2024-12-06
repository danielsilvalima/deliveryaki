<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaExpediente extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function empresa()
    {
      return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function horario_expedientes()
    {
      return $this->belongsTo(HorarioExpediente::class, 'horario_expediente_id', 'id');
    }
}
