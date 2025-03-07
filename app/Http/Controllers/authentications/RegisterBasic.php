<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\HashGenerator;
use App\Services\ViaCep\ViaCepService;
use App\Services\Fcm\FcmService;
use App\Services\Empresa\AgendaEmpresaService;
use Illuminate\Support\Str;

class RegisterBasic extends Controller
{

  public function index()
  {
    return view('content.authentications.auth-register-basic');
  }

  public function store(Request $request, FcmService $fcmService, AgendaEmpresaService $agendaEmpresaService)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|unique:users',
      'cnpj' => 'required|string|min:11|max:18|unique:empresas',
      'razao_social' => 'required|string|unique:empresas',
      'password' => 'required|min:3|max:50',
    ], [
      'email.required' => __('E-mail é obrigatório'),
      'cnpj.unique' => __('CNPJ já está cadastrado'),
      'cnpj.required' => __('CNPJ é obrigatório'),
      'cnpj.max' => __('CNPJ inválido'),
      'cnpj.min' => __('CNPJ inválido'),
      'razao_social.unique' => __('Razão Social já está cadastrado'),
      'razao_social.required' => __('Razão Social é obrigatório'),
      'celular.required' => __('Celular é obrigatório'),
      'email.unique' => __('E-mail já está cadastrado'),
      'password.required' => __('Senha é obrigatório'),
    ]);

    if ($validator->fails()) {
      return redirect()->back()
        ->withErrors($validator)
        ->withInput();
    }

    DB::beginTransaction();

    $empresa = $request->only('razao_social', 'cnpj', 'celular',  'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf');
    $empresa['status'] = 'A';
    $empresa['celular'] = Str::replace(' ', '', Str::replace('.', '', Str::replace(')', '', Str::replace('(', '', Str::replace('-', '', $empresa['celular'])))));

    do {
      $empresa['hash'] = HashGenerator::generateUniqueHash8Caracter();
    } while (Empresa::where('hash', $empresa['hash'])->exists());

    if ($empresa_id = Empresa::create($empresa)->id) {

      $usuario = $request->only('email', 'password');
      $usuario['empresa_id'] = $empresa_id;

      if (!User::create($usuario)) {
        DB::rollBack();
      } else {
        DB::commit();

        $empresa_admin = $agendaEmpresaService->findByEmailSummary('daniel.silvalima89@gmail.com');
        $mensagem = 'Novo login deliveryaki empresa:' . $empresa['razao_social'] . ' - e-mail:' . $usuario['email'];
        $fcmService->enviaPushNotificationAgendaAdmin($empresa_admin, $mensagem, 'Novo Login');

        if (!Auth::attempt($request->only('email', 'password'))) {
          return redirect()->back();
        }

        $request->user()->createToken('invoice');
        return redirect()->route('dashboard-analytics');
      }
    }

    return back();
  }

  public function getCEP(Request $request, ViaCepService $viaCepService)
  {
    $cep = $request->input('cep');

    // Valide o CEP
    if (!preg_match('/^[0-9]{8}$/', $cep)) {
      return response()->json(['success' => false, 'message' => 'CEP inválido.'], 400);
    }

    try {
      $dados = $viaCepService->findViaCep($cep);

      // Retorne os dados para o frontend
      return response()->json([
        'success' => true,
        'logradouro' => $dados['logradouro'],
        'complemento' => $dados['complemento'],
        'bairro' => $dados['bairro'],
        'cidade' => $dados['cidade'],
        'uf' => $dados['uf']
      ]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
  }
}
