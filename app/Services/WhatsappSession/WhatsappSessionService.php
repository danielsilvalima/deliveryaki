<?php

namespace App\Services\WhatsappSession;

use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class WhatsappSessionService
{
  public function removeCaracteres($valor)
  {
    return preg_replace('/\D/', '', $valor);
  }
}
