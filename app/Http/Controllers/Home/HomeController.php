<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Home\HomeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
  public function index(HomeService $homeService)
  {
    $data_fim = Carbon::now()->toDateString(); // Data atual
    $indicadores = $homeService->getIndicadores(Auth::user()->empresa_id, $data_fim, $data_fim);

    return view('content.dashboard.dashboards-analytics', [
      'indicadores' => $indicadores,
      'email' => Auth::user()->email,
      'data_inicio' => $data_fim,
      'data_fim' => $data_fim
    ]);
  }

  public function post(Request $request, HomeService $homeService)
  {
    try {
      if ($request->data_inicio === null || $request->data_fim === null) {
        return back()->with('error', 'PREENCHA O CAMPO DE DATA INICIAL E FINAL');
      }

      $indicadores = $homeService->getIndicadores(Auth::user()->empresa_id, $request->data_inicio, $request->data_fim);

      return view('content.dashboard.dashboards-analytics', [
        'indicadores' => $indicadores,
        'email' => Auth::user()->email,
        'data_inicio' => $request->data_inicio,
        'data_fim' => $request->data_fim
      ]);
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL PESQUISAR. ' . $e);
    }
  }
}
