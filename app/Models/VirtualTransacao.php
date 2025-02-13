<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualTransacao extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function virtual_user()
  {
    return $this->belongsTo(VirtualUser::class, 'virtual_user_id');
  }
}
