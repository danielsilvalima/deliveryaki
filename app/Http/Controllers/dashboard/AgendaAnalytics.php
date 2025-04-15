<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\AgendaCliente;
use App\Models\AgendaClienteAgendamento;
use App\Models\AgendaEmpresa;
use App\Models\AgendaEmpresaRecurso;
use App\Models\AgendaEmpresaServico;
use App\Models\AgendaUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AgendaAnalytics extends Controller
{
  public function get(Request $request)
  {
    try {
      $email = $request->input('email');

      $user = AgendaUser::where('email', $email)->first();
      if (!$user) {
        return response()->json(['error' => 'Usuário não encontrado.'], Response::HTTP_NOT_FOUND);
      }
      $empresa = AgendaUser::find($user->empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }

      $dataInicial = Carbon::parse($request->input('dataInicial', now()->startOfMonth()))->toDateString() . ' 00:00:00';
      $dataFinal = Carbon::parse($request->input('dataFinal', now()->startOfMonth()))->toDateString() . ' 23:59:59';

      $clienteAgendamentosAtivos = AgendaClienteAgendamento::where('status', 'A')
        ->where('empresa_id', $empresa->id)
        ->whereBetween('start_scheduling_at', [$dataInicial, $dataFinal])
        ->when($request->filled('recurso'), function ($q) use ($request) {
          $q->whereIn('empresa_recurso_id', $request->input('recurso', []));
        })
        ->get();

      $clienteAgendamentosCancelados = AgendaClienteAgendamento::where('status', 'C')
        ->where('empresa_id', $empresa->id)
        ->whereBetween('start_scheduling_at', [$dataInicial, $dataFinal])
        ->when($request->filled('recurso'), function ($q) use ($request) {
          $q->whereIn('empresa_recurso_id', $request->input('recurso', []));
        })
        ->get();

      $servicos = AgendaEmpresaServico::where('empresa_id', $empresa->id)->get();
      $recursos = AgendaEmpresaRecurso::where('empresa_id', $empresa->id)->get();

      // Indicadores
      $totalAgendamentosAtivos = $clienteAgendamentosAtivos->count();
      $totalAgendamentosCancelamentos = $clienteAgendamentosCancelados->count();
      $servicosAtivos = $servicos->where('status', 'A')->count();
      $recursosAtivos = $recursos->where('status', 'A')->count();

      // Agendamentos por serviço
      $agendamentosPorServico = [];
      foreach ($clienteAgendamentosAtivos as $agendamento) {
        $servico = $servicos->firstWhere('id', $agendamento->empresa_servico_id);
        $descricao = $servico ? $servico->descricao : 'Desconhecido';
        if (!isset($agendamentosPorServico[$descricao])) {
          $agendamentosPorServico[$descricao] = 0;
        }
        $agendamentosPorServico[$descricao]++;
      }

      // Agendamentos por dia
      $agendamentosPorDia = [];
      foreach ($clienteAgendamentosAtivos as $agendamento) {
        $data = \Carbon\Carbon::parse($agendamento->start_scheduling_at)->format('d/m/Y');
        if (!isset($agendamentosPorDia[$data])) {
          $agendamentosPorDia[$data] = 0;
        }
        $agendamentosPorDia[$data]++;
      }

      return response()->json([
        'totalAgendamentosAtivos' => $totalAgendamentosAtivos,
        'totalAgendamentosCancelados' => $totalAgendamentosCancelamentos,
        'servicosAtivos' => $servicosAtivos,
        'recursosAtivos' => $recursosAtivos,
        'agendamentosPorServico' => $agendamentosPorServico,
        'agendamentosPorDia' => $agendamentosPorDia,
      ]);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
