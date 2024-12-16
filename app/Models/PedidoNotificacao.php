<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoNotificacao extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function empresa()
    {
      return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Relacionamento com a tabela Pedido.
     */
    public function pedido()
    {
      return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
