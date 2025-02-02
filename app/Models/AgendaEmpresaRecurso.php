<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaEmpresaRecurso extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function agenda_empresa_servicos()
    {
        return $this->hasMany(AgendaEmpresaServico::class, 'recurso_id');
    }
}
