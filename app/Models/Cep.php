<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cep extends Model
{
    use HasFactory;

    //protected $fillable = ['id', 'cep', 'logradouro', 'complemento', 'bairro', 'cidade', 'uf'];
    protected $guarded = [];
}
