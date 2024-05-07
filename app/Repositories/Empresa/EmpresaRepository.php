<?php

namespace App\Repositories\Empresa;

use App\Models\Empresa;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaRepository
{
	private $model;

	public function __construct(Empresa $model)
	{
		$this->model = $model;
	}

	public function findAll()
	{
    return $this->model->all();
	}

  public function findByID(string $id)
	{
    return $this->model->where('id', '=', $id)->first();
	}

  public function findByUUID(string $id)
	{
    return $this->model->where('uuid', '=', $id)->first();
	}


}
