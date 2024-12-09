<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\HashGenerator;
use App\Services\EmpresaExpediente\EmpresaExpedienteService;
use App\Services\HorarioExpediente\HorarioExpedienteService;
use App\Helpers\ResponseHelper;
use App\Models\EmpresaExpediente;
use Illuminate\Support\Facades\DB;

class EmpresaController extends Controller
{
  public function index(Empresa $empresa)
  {
    $empresas = Empresa::select('*')->where('id', Auth::user()->empresa_id)->get();
    return view('content.empresa.index', [
      'empresas' => $empresas,
      'email' => Auth::user()->email
    ]);
  }

  public function create()
  {
    try{
      return view('content.empresa.create')->with([
          'email' => Auth::user()->email,
      ]);
    } catch (\Exception $e) {
      return redirect()->route('empresa.index')->with('error', 'NÃO FOI POSSÍVEL CADASTRAR A EMPRESA. '.$e);
    }
  }

  public function store(Request $request, Empresa $empresa)
  {
    $data = $request->post();

    do {
      $data['hash'] = HashGenerator::generateUniqueHash8Caracter();
    } while ($empresa->where('hash', $data['hash'])->exists());

    $empresa->create($data);

    return redirect()->route('empresa.index')->with('success', 'EMPRESA CADASTRADO COM SUCESSO');
  }

  public function edit(Request $request, string $id, Empresa $empresa)
  {
    DB::beginTransaction();
    try{
      if (!$empresa = $empresa->find($id)) {
        return back()->with('error', 'EMPRESA NÃO FOI LOCALIZADA');
      }

      $empresa->update($request->only([
        'cnpj', 'razao_social', 'telefone', 'celular', 'email', 'status', 'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'vlr_km', 'tipo_taxa', 'inicio_distancia'
      ]));

      $expedientes = json_decode($request->input('expedientes'), true);

      if (!empty($expedientes)) {
        // Exclui os expedientes antigos da tabela
        EmpresaExpediente::where('empresa_id', $empresa->id)->delete();

        // Cria os novos expedientes na tabela
        foreach ($expedientes as $expediente) {
            EmpresaExpediente::create([
                'empresa_id' => $empresa->id,
                'horario_expediente_id' => $expediente['horario_expediente_id'],
                'hora_abertura' => $expediente['hora_abertura'],
                'hora_fechamento' => $expediente['hora_fechamento'],
                'intervalo_inicio' => $expediente['intervalo_inicio'],
                'intervalo_fim' => $expediente['intervalo_fim'],
            ]);
        }
      } else {
          // Se não houver novos expedientes, exclui os antigos
          EmpresaExpediente::where('empresa_id', $empresa->id)->delete();
      }

      DB::commit();

      return redirect()->route('empresa.index')->with('success', 'EMPRESA ATUALIZADO COM SUCESSO');
    } catch (\Exception $e) {
      DB::rollBack();
      //throw new \Exception('ERRO AO EDITAR A EMPRESA ' . $e->getMessage());
      return back()->with('error', 'EMPRESA NÃO FOI ATUALIZADA. '.$e);
    }
  }

  public function show(Empresa $empresa, string|int $id, HorarioExpedienteService $horarioExpedienteService, EmpresaExpedienteService $empresaExpedienteService)
  {
    //if (!$empresa = $empresa->where('id', $id)->where('id', Auth::user()->empresa_id)->first()) {
    if(!$empresa = Empresa::with([
      'empresa_expedientes.horario_expedientes'
      ])->findOrFail($id)){
      return back()->with('error', ' NÃO FOI POSSÍVEL BUSCAR A EMPRESA');
    }

    $horarioExpedientes = $horarioExpedienteService->findAll();

    //$empresaExpedientes = $empresaExpedienteService->findAllByEmpresaID(Auth::user()->empresa_id);

    return view('content.empresa.show', compact(('empresa')))->with([
      'email' => Auth::user()->email,
      'horarioExpedientes' => $horarioExpedientes,
      'empresaExpedientes' => $empresa->empresa_expedientes
    ]);
  }

  public function modal(string $id, Empresa $empresa)
  {
    if (!$empresa = $empresa->where('id', $id)->where('id', Auth::user()->empresa_id)) {
      return back()->with('error', ' NÃO FOI POSSÍVEL BUSCAR A EMPRESA');
    }

    return redirect()->route('empresa.index')->with(['empresa' => $empresa]);
  }
}
