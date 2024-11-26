<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Home\HomeService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
  public function index(HomeService $homeService)
  {
      // Obtem os indicadores a partir do serviço
      $indicadores = $homeService->getIndicadores(Auth::user()->empresa_id);

      return view('content.dashboard.dashboards-analytics', [
          'indicadores' => $indicadores,
          'email' => Auth::user()->email
      ]);
  }
}
