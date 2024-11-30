<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Categoria extends Model
{
  use HasFactory;

  //protected $fillable = ['id', 'descricao', 'status', 'empresa_id'];
  protected $guarded = [];

  public function produtos()
    {
        return $this->hasMany(Produto::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

}
