<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaEmpresaServico extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function agenda_empresa()
    {
      return $this->belongsTo(AgendaEmpresa::class, 'empresa_id');
    }

    public function agenda_servicos()
    {
      return $this->belongsTo(AgendaServico::class, 'servico_id', 'id');
    }
}
