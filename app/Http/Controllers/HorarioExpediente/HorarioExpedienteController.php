<?php

namespace App\Http\Controllers\HorarioExpediente;

use App\Http\Controllers\Controller;
use App\Models\HorarioExpediente;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HorarioExpedienteController extends Controller
{
  public function get(Request $request)
  {
    try {
      $horarioExpediente = HorarioExpediente::get();

      return response()->json(
        $horarioExpediente,
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
