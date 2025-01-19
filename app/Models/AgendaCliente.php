<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaCliente extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function agenda_empresa()
    {
        return $this->belongsTo(AgendaEmpresa::class, 'empresa_id');
    }

    public function agenda_cliente_agendamentos()
    {
        return $this->hasMany(AgendaClienteAgendamento::class, 'cliente_id');
    }
}
