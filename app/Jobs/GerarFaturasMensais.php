<?php

namespace App\Jobs;

use App\Models\AgendaEmpresa;
use App\Models\Empresa;
use App\Models\Fatura;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GerarFaturasMensais implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   */
  public function __construct()
  {
    //
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    $hoje = Carbon::now();
    $mesAnterior = $hoje->copy()->subMonth();
    $referencia = $mesAnterior->format('m/Y');
    $vencimento = $hoje->copy()->startOfMonth()->addDays(10);

    // Agendamento
    $agendaEmpresas = AgendaEmpresa::where('status', 'A')->get();
    foreach ($agendaEmpresas as $empresa) {
      $dataCriacao = Carbon::parse($empresa->created_at)->addDays(15);
      if (!$this->faturaExiste($empresa->id, 'agendaadmin', $referencia) && $dataCriacao->lt($hoje->copy()->startOfMonth())) {
        $valorCheio = $this->valorPlanoAgenda($empresa->plano_recurso);
        $valor_a_pagar = $this->calcularValorComercial($dataCriacao, $mesAnterior, $valorCheio);

        Fatura::create([
          'empresa_id' => $empresa->id,
          'tipo_app' => 'agendaadmin',
          'referencia' => $referencia,
          'valor_a_pagar' => $valor_a_pagar,
          'valor_total' => $valorCheio,
          'status' => 'pendente',
          'vencimento' => $vencimento,
        ]);
      }
    }

    // Delivery
    $empresasDelivery = Empresa::where('status', 'A')->get();
    foreach ($empresasDelivery as $empresa) {
      $dataCriacao = Carbon::parse($empresa->created_at)->addDays(15);
      if (!$this->faturaExiste($empresa->id, 'deliveryaki', $referencia) && $dataCriacao->lt($hoje->copy()->startOfMonth())) {
        $valorCheio = $this->valorFixoDelivery();
        $valor_a_pagar = $this->calcularValorComercial($dataCriacao, $mesAnterior, $valorCheio);

        Fatura::create([
          'empresa_id' => $empresa->id,
          'tipo_app' => 'deliveryaki',
          'referencia' => $referencia,
          'valor_a_pagar' => $valor_a_pagar,
          'valor_total' => $valorCheio,
          'status' => 'pendente',
          'vencimento' => $vencimento,
        ]);
      }
    }
  }

  private function faturaExiste($empresaId, $tipo, $referencia): bool
  {
    return Fatura::where('empresa_id', $empresaId)
      ->where('tipo_app', $tipo)
      ->where('referencia', $referencia)
      ->exists();
  }

  private function valorPlanoAgenda($plano): float
  {
    return match ($plano) {
      '1' => 59.90,
      '2' => 89.90,
      '3' => 124.90,
      '4' => 164.90,
      default => 59.90,
    };
  }

  private function valorFixoDelivery(): float
  {
    return 69.90;
  }

  private function calcularValorComercial(Carbon $dataInicioUso, Carbon $mesReferencia, float $valorCheio): float
  {
    $inicioDoMes = $mesReferencia->copy()->startOfMonth();
    $fimDoMes = $mesReferencia->copy()->startOfMonth()->addDays(29);
    $usoInicio = $dataInicioUso->greaterThan($inicioDoMes) ? $dataInicioUso : $inicioDoMes;

    if ($usoInicio->gt($fimDoMes)) {
      return 0; // não utilizou nesse mês
    }

    $diasDeUso = $fimDoMes->diffInDays($usoInicio) + 1; // mês comercial
    $proporcional = ($valorCheio / 30) * $diasDeUso;

    return round($proporcional, 2);
  }
}
