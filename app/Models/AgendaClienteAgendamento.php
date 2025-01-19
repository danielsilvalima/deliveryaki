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

    public function agenda_servicos()
    {
        return $this->belongsTo(AgendaServico::class, 'servico_id');
    }
}
