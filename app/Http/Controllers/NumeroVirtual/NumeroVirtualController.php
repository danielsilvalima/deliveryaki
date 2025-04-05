<?php

namespace App\Http\Controllers\NumeroVirtual;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;
use App\Services\NumeroVirtual\NumeroVirtualService;
use Illuminate\Support\Facades\Log;

class NumeroVirtualController extends Controller
{
  public function store(Request $request, NumeroVirtualService $numeroVirtualService)
  {
    try {
      /*if (empty($request->id)) {
        return ResponseHelper::error('O "ID" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      if (empty($request->email)) {
        return ResponseHelper::error('O "E-MAIL" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      if (empty($request->data)) {
        return ResponseHelper::error('A "DATA" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }*/

      $numero = $numeroVirtualService->start1($request);

      return response()->json([$numero], Response::HTTP_OK);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function store2(Request $request, NumeroVirtualService $numeroVirtualService)
  {
    try {
      $numero = $numeroVirtualService->start2($request);

      return response()->json([$numero], Response::HTTP_OK);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function store3(Request $request, NumeroVirtualService $numeroVirtualService)
  {
    try {
      $numero = $numeroVirtualService->start3($request);

      return response()->json([$numero], Response::HTTP_OK);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }
}
