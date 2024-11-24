<?php

namespace App\Services\Empresa;

use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use App\Models\Empresa;

class EmpresaService
{
  public function findAll()
	{
    return Empresa::all();
	}

  public function findByID(string $id)
	{
    return Empresa::where('id', '=', $id)->first();
	}

  public function findByUUID(string $id)
	{
    return Empresa::where('uuid', '=', $id)->first();
	}

  public function findByHash(string $hash)
	{
    try{
      return Empresa::where('hash', '=', $hash)->first();
    } catch (\Exception $e) {
      throw new \Exception('Hash inválido');
    }
	}
}
