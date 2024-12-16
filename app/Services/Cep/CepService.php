<?php

namespace App\Services\Cep;

use App\Models\Cep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CepService
{
  public function findByCEP($cep)
	{
    return Cep::where('cep', '=', $cep)->first();
	}
}
