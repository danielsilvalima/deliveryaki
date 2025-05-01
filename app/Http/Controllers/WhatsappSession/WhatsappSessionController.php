<?php

namespace App\Http\Controllers\WhatsappSession;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\WhatsappSession;
use App\Services\WhatsappSession\WhatsappSessionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class WhatsappSessionController extends Controller
{
  public function get(Request $request, WhatsappSessionService $whatsappSessionService)
  {
    try {
      $empresa_id = $request->input('empresa_id');
      $empresa = Empresa::find($empresa_id);

      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }

      $nodeHost = config('services.whatsapp.host');

      // Passa o nome da sessão (padrão session-<empresa_id>)
      $sessionName = 'session-' . $whatsappSessionService->removeCaracteres($empresa->cnpj);
      $response = Http::get("{$nodeHost}/get-qrcode", [
        'session' => $sessionName
      ]);

      //if ($response->successful()) {
      $qrcodeData = $response->json();

      // Atualiza ou cria a sessão no banco
      /*WhatsappSession::updateOrCreate(
          ['empresa_id' => $empresa_id],
          [
            'session_name' => $sessionName,
            'status' => $qrcodeData['status'] === 'aguardando' ? 'pendente' : 'ativo',
            'qr_code_base64' => $qrcodeData['qrcode'],
            'last_connected_at' => null,
          ]
        );*/

      return response()->json($qrcodeData, Response::HTTP_OK);
      //}

      //return response()->json(['error' => 'Erro ao obter QR Code do WhatsApp.'], $response->status());
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getStatus(Request $request, WhatsappSessionService $whatsappSessionService)
  {
    try {
      $empresa_id = $request->input('empresa_id');
      $empresa = Empresa::find($empresa_id);

      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }

      $nodeHost = config('services.whatsapp.host');

      $sessionName = 'session-' . $whatsappSessionService->removeCaracteres($empresa->cnpj);
      $response = Http::get("{$nodeHost}/status", [
        'session' => $sessionName
      ]);

      //if ($response->successful()) {
      $statusData = $response->json();
      $status = $statusData['status'];
      return response()->json($statusData, Response::HTTP_OK);
      // Atualiza status no banco
      $whatsappSession = WhatsappSession::where('empresa_id', $empresa_id)->first();
      if ($whatsappSession) {
        $whatsappSession->status = $status === 'conectado' ? 'ativo' : ($status === 'aguardando' ? 'pendente' : 'desconectado');

        if ($status === 'conectado') {
          $whatsappSession->last_connected_at = now();
        }
        $whatsappSession->save();
      }

      return response()->json($statusData, Response::HTTP_OK);
      //}

      return response()->json(['error' => 'Erro ao obter status do WhatsApp.'], $response->status());
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function logout(Request $request, WhatsappSessionService $whatsappSessionService)
  {
    try {
      $empresa_id = $request->input('empresa_id');
      $empresa = Empresa::find($empresa_id);

      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }

      $nodeHost = config('services.whatsapp.host');

      $sessionName = 'session-' . $whatsappSessionService->removeCaracteres($empresa->cnpj);
      $response = Http::post("{$nodeHost}/logout", [
        'session' => $sessionName
      ]);

      if ($response->successful()) {
        // Atualiza status da sessão no banco
        $whatsappSession = WhatsappSession::where('empresa_id', $empresa_id)->first();
        if ($whatsappSession) {
          $whatsappSession->status = 'desconectado';
          $whatsappSession->save();
        }

        return response()->json($response->json(), Response::HTTP_OK);
      }

      return response()->json(['error' => 'Erro ao desconectar do WhatsApp.'], $response->status());
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
