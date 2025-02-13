<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualUser extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function virtual_transacaos()
  {
    return $this->hasMany(VirtualTransacao::class, 'virtual_user_id');
  }
}
