<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunTaskEveryMinute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-task-every-minute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      \Log::info('Task executed at: ' . now());

      // Exemplo: Atualizar status de pedidos
      // \App\Models\Order::updateExpiredOrders();

      return 0;
    }
}
