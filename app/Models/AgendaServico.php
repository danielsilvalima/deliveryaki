<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaServico extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function agenda_empresa_servicos()
    {
      return $this->hasMany(AgendaEmpresaServico::class, 'servico_id', 'id');
    }

    public function agenda_empresa()
    {
      return $this->belongsTo(AgendaEmpresa::class, 'empresa_id');
    }
}
