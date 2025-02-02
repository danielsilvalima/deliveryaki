<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaClienteAgendamento extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function agenda_empresa()
    {
        return $this->belongsTo(AgendaEmpresa::class, 'empresa_id');
    }

    public function agenda_clientes()
    {
        return $this->belongsTo(AgendaCliente::class, 'cliente_id');
    }

    public function agenda_empresa_servicos()
    {
        return $this->belongsTo(AgendaEmpresaServico::class, 'empresa_servico_id');
    }

    public function agenda_empresa_expediente()
    {
        return $this->belongsTo(AgendaEmpresaExpediente::class, 'empresa_expediente_id');
    }

    public function agenda_empresa_recursos()
    {
        return $this->belongsTo(AgendaEmpresaRecurso::class, 'empresa_recurso_id');
    }
}
