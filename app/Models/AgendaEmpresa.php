<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AgendaEmpresa extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['agenda_empresa_expedientes'];

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

  public function getListaExpedientesAttribute()
{
  return $this->agenda_empresa_expedientes->map(function ($expediente) {
      return [
          'horario_expediente_id' => $expediente->horario_expediente_id,
          'hora_abertura' => $expediente->hora_abertura,
          'hora_fechamento' => $expediente->hora_fechamento,
          'intervalo_inicio' => $expediente->intervalo_inicio,
          'intervalo_fim' => $expediente->intervalo_fim,
      ];
  })->toArray();
}
}
