<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaHorarioExpediente extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function agenda_empresa_expedientes()
    {
      return $this->hasMany(AgendaEmpresaExpediente::class, 'horario_expediente_id');
    }
}
