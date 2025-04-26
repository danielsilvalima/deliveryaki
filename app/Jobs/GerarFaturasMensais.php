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
    $vencimento = $hoje->copy()->startOfMonth()->addDays(9);

    // Agendamento
    $agendaEmpresas = AgendaEmpresa::where('status', 'A')->get();
    foreach ($agendaEmpresas as $empresa) {

      if (!$this->faturaExiste($empresa->id, 'agendaadmin', $referencia)) {
        $valorCheio = $this->valorPlanoAgenda($empresa->plano_recurso);

        Fatura::create([
          'empresa_id' => $empresa->id,
          'tipo_app' => 'agendaadmin',
          'referencia' => $referencia,
          'valor_a_pagar' => $valorCheio,
          'valor_total' => $valorCheio,
          'status' => 'pendente',
          'vencimento' => $vencimento,
        ]);
      }
    }

    // Delivery
    $empresasDelivery = Empresa::where('status', 'A')->get();
    foreach ($empresasDelivery as $empresa) {

      if (!$this->faturaExiste($empresa->id, 'deliveryaki', $referencia)) {
        $valorCheio = $this->valorFixoDelivery();

        Fatura::create([
          'empresa_id' => $empresa->id,
          'tipo_app' => 'deliveryaki',
          'referencia' => $referencia,
          'valor_a_pagar' => $valorCheio,
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
}
