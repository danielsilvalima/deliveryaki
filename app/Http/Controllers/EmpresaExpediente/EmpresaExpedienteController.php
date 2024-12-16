<?php

namespace App\Http\Controllers\EmpresaExpediente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmpresaExpediente;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;

class EmpresaExpedienteController extends Controller
{
  public function destroy(string $id, EmpresaExpediente $empresaExpediente)
  {return $id;
    try{
      $empresaExpediente = EmpresaExpediente::findOrFail($id);

      $empresaExpediente->delete();

      return redirect()->route('empresa.edit', $empresaExpediente->empresa_id);
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL ATUALIZAR O EXPEDIENTE. '.$e);
    }
  }
}
