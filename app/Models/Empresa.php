<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Empresa extends Model
{
  use HasFactory;

  //protected $fillable = ['id', 'uuid', 'cnpj', 'razao_social', 'telefone', 'celular', 'status', 'hash', 'logradouro', 'complemento', 'bairro', 'numero', 'cidade', 'uf'];
  protected $guarded = [];

  public static function booted()
  {
    static::creating(function ($model) {
      $model->uuid = Str::uuid();
    });
  }

  public function produtos()
    {
      return $this->hasMany(Produto::class);
    }

    public function categorias()
    {
      return $this->hasMany(Categoria::class);
    }

    public function pedidos()
    {
      return $this->hasMany(Pedido::class);
    }

    public function clientes()
    {
      return $this->hasMany(Cliente::class);
    }

    public function empresa_expedientes()
    {
      return $this->hasMany(EmpresaExpediente::class, 'empresa_id', 'id');
    }

    public function pedido_notificacaos()
    {
      return $this->hasMany(PedidoNotificacao::class, 'empresa_id');
    }
}
