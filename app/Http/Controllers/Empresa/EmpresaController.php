<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\HashGenerator;
use App\Services\EmpresaExpediente\EmpresaExpedienteService;
use App\Services\Empresa\EmpresaService;
use App\Services\HorarioExpediente\HorarioExpedienteService;
use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class EmpresaController extends Controller
{
  public function index(Empresa $empresa)
  {
    $empresas = Empresa::select('*')
      ->where('id', Auth::user()->empresa_id)
      ->get();
    return view('content.empresa.index', [
      'empresas' => $empresas,
      'email' => Auth::user()->email,
    ]);
  }

  public function create()
  {
    try {
      return view('content.empresa.create')->with([
        'email' => Auth::user()->email,
      ]);
    } catch (\Exception $e) {
      return redirect()
        ->route('empresa.index')
        ->with('error', 'NÃO FOI POSSÍVEL CADASTRAR A EMPRESA. ' . $e);
    }
  }

  /*public function store(Request $request, Empresa $empresa, EmpresaService $empresaService)
  {
    $data = $request->post();

    $request->validate([
      'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Máximo 5MB
    ]);

    $expiration = Carbon::now()->addDays(30);

    do {
      $data['hash'] = HashGenerator::generateUniqueHash8Caracter();
    } while ($empresa->where('hash', $data['hash'])->exists());

    $data['expiration_at'] = $expiration;
    $data['celular'] = $empresaService->removeCaracteres($data['celular']);
    $data['cnpj'] = $empresaService->removeCaracteres($data['cnpj']);

    if ($request->hasFile('logo')) {
      $directory = "public/logo/{$empresa->cnpj}";
      $file = $request->file('logo');
      $filename = uniqid() . '_' . $file->getClientOriginalName(); // Gera nome único
      $filePath = $file->storeAs($directory, $filename); // Salva em storage/app/public/logos
      $data['path'] = $filePath;
    }

    Empresa::create($data);

    return redirect()
      ->route('empresa.index')
      ->with('success', 'EMPRESA CADASTRADO COM SUCESSO');
  }*/

  public function edit(Request $request, string $id, Empresa $empresa, EmpresaService $empresaService)
  {
    try {
      $request->validate([
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Máximo 5MB
      ]);

      if (!($empresa = $empresa->find($id))) {
        return back()->with('error', 'EMPRESA NÃO FOI LOCALIZADA');
      }

      $empresa = $empresaService->update($request, $empresa);

      return redirect()
        ->route('empresa.index')
        ->with('success', 'EMPRESA ATUALIZADO COM SUCESSO');
    } catch (\Exception $e) {
      //throw new \Exception('ERRO AO EDITAR A EMPRESA ' . $e->getMessage());
      return back()->with('error', 'EMPRESA NÃO FOI ATUALIZADA. ' . $e);
    }
  }

  public function show(
    Empresa $empresa,
    string|int $id,
    HorarioExpedienteService $horarioExpedienteService,
    EmpresaExpedienteService $empresaExpedienteService
  ) {
    //if (!$empresa = $empresa->where('id', $id)->where('id', Auth::user()->empresa_id)->first()) {
    if (!($empresa = Empresa::with(['empresa_expedientes.horario_expedientes'])->findOrFail($id))) {
      return back()->with('error', ' NÃO FOI POSSÍVEL BUSCAR A EMPRESA');
    }

    $horarioExpedientes = $horarioExpedienteService->findAll();

    //$empresaExpedientes = $empresaExpedienteService->findAllByEmpresaID(Auth::user()->empresa_id);

    return view('content.empresa.show', compact('empresa'))->with([
      'email' => Auth::user()->email,
      'horarioExpedientes' => $horarioExpedientes,
      'empresaExpedientes' => $empresa->empresa_expedientes,
    ]);
  }

  public function modal(string $id, Empresa $empresa)
  {
    if (!($empresa = $empresa->where('id', $id)->where('id', Auth::user()->empresa_id))) {
      return back()->with('error', ' NÃO FOI POSSÍVEL BUSCAR A EMPRESA');
    }

    return redirect()
      ->route('empresa.index')
      ->with(['empresa' => $empresa]);
  }

  public function deleteLogo(string $id, Empresa $empresa, EmpresaService $empresaService)
  {
    $empresa = Empresa::findOrFail($id);

    if ($empresa->path) {
      $empresaService->deleteOldFile($empresa->id);
      $empresa->path = null;
      $empresa->save();
      return response()->json([
        'success' => true,
        'message' => 'LOGO REMOVIDO COM SUCESSO, NÃO É NECESSÁRIO SALVAR O CADASTRO',
      ]);
    } else {
      return response()->json(['success' => true, 'message' => 'NÃO HÁ LOGO PARA SER REMOVIDO']);
    }
  }

  public function login(Request $request)
  {
    $request->validate([
      'email' => 'required|email',
      'password' => 'required',
    ]);

    $usuario = User::where('email', $request->email)->where('status', 'A')->first();

    if (!$usuario || !Hash::check($request->password, $usuario->password)) {
      return response()->json(['message' => 'Credenciais inválidas'], 401);
    }

    $empresa = Empresa::find($usuario->empresa_id);

    $token = $usuario->createToken('user-token')->plainTextToken;

    return response()->json([
      'message' => 'Login realizado com sucesso',
      'token' => $token,
      'usuario' => $usuario,
      'empresa' => $empresa,
    ]);
  }

  public function get(Request $request)
  {
    try {
      $id = $request->input('empresa_id');
      $limit = $request->input('limit', 10);
      $page = $request->input('page', 1);
      $query = Empresa::with(['empresa_expedientes.horario_expedientes']);
      $query->where('id', $id);

      $itensPaginados = $query->paginate($limit, ['*'], 'page', $page);

      return response()->json(
        [
          'current_page' => $itensPaginados->currentPage(),
          'data' => $itensPaginados->items(),
          'total_pages' => $itensPaginados->lastPage(),
          'total' => $itensPaginados->total(),
          'per_page' => $itensPaginados->perPage(),
        ],
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function update(Request $request, EmpresaService $empresaService)
  {
    try {
      $empresa_id = $request->empresa_id;

      $empresa = Empresa::find($empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      if ($empresaService->validaDataExpiracao($empresa)) {
        return response()->json(
          ['error' => 'A empresa está expirada e não pode atualizar empresa.'],
          Response::HTTP_FORBIDDEN
        );
      }

      $request->merge([
        'vlr_km' => Str::replace(',', '.', $request->vlr_km),
      ]);

      $empresa_db = $empresaService->update($request, $empresa);

      return response()->json(
        ['message' => 'Empresa atualizado com sucesso.', 'empresa' => $empresa_db],
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function store(Request $request, EmpresaService $empresaService)
  {
    DB::beginTransaction();
    try {
      $empresa = $request->input('empresa');
      $email = $empresa['email'];
      $password = $empresa['password'];

      $empresa['celular'] = $empresaService->removeCaracteres($empresa['celular']);
      $empresa['cnpj'] = $empresaService->removeCaracteres($empresa['cnpj']);

      $empresaExistente = Empresa::where('cnpj', $empresa['cnpj'])->first();
      if ($empresaExistente) {
        return response()->json(['error' => 'Este CNPJ já está cadastrado.'], Response::HTTP_FORBIDDEN);
      }

      $emailExistente = User::where('email', $email)->first();
      if ($emailExistente) {
        return response()->json(['error' => 'Este E-mail já está cadastrado.'], Response::HTTP_FORBIDDEN);
      }

      $empresa['vlr_km'] = Str::replace(',', '.', $empresa['vlr_km']);

      $expiration = Carbon::now()->addDays(15);

      do {
        $empresa['hash'] = HashGenerator::generateUniqueHash8Caracter();
      } while (\App\Models\Empresa::where('hash', $empresa['hash'])->exists());

      $empresa['expiration_at'] = $expiration;


      unset($empresa['email'], $empresa['password']);

      $empresa_db = Empresa::create($empresa);

      $usuario = [
        "email" => $email,
        "password" => $password,
        "empresa_id" => $empresa_db->id
      ];
      User::create($usuario);

      DB::commit();
      return response()->json(
        ['message' => 'Empresa cadastrada com sucesso.', 'empresa' => $empresa_db],
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
