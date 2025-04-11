<?php

namespace App\Http\Controllers\Mesa;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MesaController extends Controller
{
  public function get(Request $request)
  {
    try {
      $mesas = Mesa::get();

      return response()->json(
        $mesas,
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
