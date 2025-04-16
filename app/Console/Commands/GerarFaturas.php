<?php

namespace App\Console\Commands;

use App\Jobs\GerarFaturasMensais;

use Illuminate\Console\Command;

class GerarFaturas extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:gerar-faturas';
  protected $description = 'Gera faturas mensais para empresas ativas';

  /**
   * The console command description.
   *
   * @var string
   */

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $this->info('Iniciando geração de faturas...');

    // Dispara o job
    GerarFaturasMensais::dispatch();

    $this->info('Faturas geradas com sucesso!');
  }
}
