<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AgendaEmpresa extends Model
{
    use HasFactory;

    protected $guarded = [];

  public static function booted()
  {
    static::creating(function ($model) {
      $model->uuid = Str::uuid();
    });
  }

  public function agenda_user()
  {
    return $this->hasOne(AgendaUser::class, 'empresa_id', 'id');
  }

  public function agenda_empresa_expedientes()
  {
    return $this->hasMany(AgendaEmpresaExpediente::class, 'empresa_id', 'id');
  }

  public function agenda_empresa_servicos()
  {
    return $this->hasMany(AgendaEmpresaServico::class, 'empresa_id', 'id');
  }

  public function agenda_clientes()
  {
    return $this->hasMany(AgendaCliente::class, 'empresa_id', 'id');
  }

  public function agenda_cliente_agendamentos()
  {
    return $this->hasMany(AgendaClienteAgendamento::class, 'empresa_id', 'id');
  }

  public function agenda_servicos()
    {
      return $this->hasMany(AgendaServico::class, 'empresa_id', 'id');
    }

}
