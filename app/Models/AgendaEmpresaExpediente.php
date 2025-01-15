<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaEmpresaExpediente extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function agenda_empresa()
    {
      return $this->belongsTo(AgendaEmpresa::class, 'empresa_id');
    }

    public function agenda_horario_expedientes()
    {
      return $this->belongsTo(AgendaHorarioExpediente::class, 'horario_expediente_id', 'id');
    }
}
