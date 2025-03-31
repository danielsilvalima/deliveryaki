<?php

use App\Http\Controllers\Categoria\CategoriaController;
use App\Http\Controllers\Cep\CepController;
use App\Http\Controllers\Empresa\AgendaEmpresaController;
use App\Http\Controllers\Empresa\EmpresaController;
use App\Http\Controllers\HorarioExpediente\AgendaHorarioExpedienteController;
use App\Http\Controllers\EmpresaExpediente\EmpresaExpedienteController;
use App\Http\Controllers\Servico\AgendaServicoController;
use App\Http\Controllers\Cliente\AgendaClienteController;
use App\Http\Controllers\HorarioExpediente\HorarioExpedienteController;
use App\Http\Controllers\Recurso\AgendaRecursoController;
use App\Http\Controllers\NumeroVirtual\NumeroVirtualController;
use App\Http\Controllers\Pedido\PedidoController;
use App\Http\Controllers\Produto\ProdutoController;
use App\Http\Controllers\Produto\StoreProdutoController;
use App\Http\Controllers\Usuario\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

//API FRONT
Route::middleware('api.keyagenda')->group(function () {
  Route::get('/agenda/empresa', [AgendaEmpresaController::class, 'get'])->name('agenda.get');
  Route::get('/agenda/empresa/login', [AgendaEmpresaController::class, 'getLogin'])->name('agenda.getLogin');
  Route::get('/agenda/empresa/agendamento', [AgendaEmpresaController::class, 'getByID'])->name('agenda.getByID');
  Route::post('/agenda/empresa', [AgendaEmpresaController::class, 'store'])->name('agenda.store');
  Route::post('/agenda/empresa/auth', [AgendaEmpresaController::class, 'updateToken'])->name('agenda.updateToken');
  Route::get('/agenda/horario-expediente', [AgendaHorarioExpedienteController::class, 'get'])->name(
    'horarioexpediente.get'
  );

  Route::post('/agenda/expediente', [EmpresaExpedienteController::class, 'store'])->name('expediente.store');
  Route::get('/agenda/expediente', [EmpresaExpedienteController::class, 'get'])->name('expediente.get');

  Route::get('/agenda/servico', [AgendaServicoController::class, 'get'])->name('servico.get');
  Route::get('/agenda/empresa/servico', [AgendaServicoController::class, 'getByIDEmpresaResource'])->name(
    'servico.getByIDEmpresaResource'
  );
  Route::post('/agenda/servico', [AgendaServicoController::class, 'store'])->name('servico.store');

  Route::get('/agenda/recurso', [AgendaRecursoController::class, 'get'])->name('recurso.get');
  Route::post('/agenda/recurso', [AgendaRecursoController::class, 'store'])->name('recurso.store');

  Route::get('/agenda/empresa/cliente', [AgendaClienteController::class, 'getByIDEmail'])->name('agenda.getByIDEmail');

  Route::get('/agenda/empresa/cliente/servico', [AgendaClienteController::class, 'getClienteByIDEmail'])->name(
    'agenda.getClienteByIDEmail'
  );

  Route::get('/agenda/empresa/cliente/agenda/agendamento', [
    AgendaClienteController::class,
    'getAgendamentoByIDEmail',
  ])->name('agenda.getAgendamentoByIDEmail');
  Route::get('/agenda/empresa/cliente/agenda', [AgendaClienteController::class, 'get'])->name('agendaCliente.get');
  Route::post('/agenda/empresa/cliente/agenda', [AgendaClienteController::class, 'store'])->name('agendaCliente.store');
  Route::delete('/agenda/empresa/cliente/agenda', [AgendaClienteController::class, 'destroy'])->name('agendaCliente.destroy');


  //store
  Route::get('/store/produto/banner', [StoreProdutoController::class, 'getBanner'])->name('store.getBanner');

  Route::post('/store/produto', [StoreProdutoController::class, 'store'])->name('store.store');
  Route::delete('/store/produto/{id}', [StoreProdutoController::class, 'delete'])->name('store.delete');
  Route::post('/store/produto/clique', [StoreProdutoController::class, 'storeClique'])->name('store.storeClique');
  Route::get('/store/produto', [StoreProdutoController::class, 'get'])->name('store.get');
  Route::get('/store/produto/{id}', [StoreProdutoController::class, 'getByID'])->name('store.getByID');
});

Route::middleware('api.keypedido')->post('/deliveryaki/login', [EmpresaController::class, 'login'])->name('login.login');
Route::middleware('api.keypedido')->get('/deliveryaki/cep/cep', [CepController::class, 'getCEP'])->name('cep.getCEP');
Route::middleware('api.keypedido')->get('/deliveryaki/cep', [CepController::class, 'get'])->name('cep.get');
Route::middleware('api.keypedido')->post('/deliveryaki/empresa', [EmpresaController::class, 'store'])->name('empresa.store');

Route::middleware(['api.keypedido', 'auth:sanctum'])->group(function () {
  Route::get('/deliveryaki/categoria', [CategoriaController::class, 'get'])->name('categoria.get');
  Route::post('/deliveryaki/categoria', [CategoriaController::class, 'store'])->name('categoria.store');
  Route::put('/deliveryaki/categoria/{id}', [CategoriaController::class, 'update'])->name('categoria.update');
  Route::put('/deliveryaki/categoria/{id}/status', [CategoriaController::class, 'updateStatus'])->name('categoria.updateStatus');

  Route::post('/deliveryaki/produto', [ProdutoController::class, 'store'])->name('produto.store');
  Route::post('/deliveryaki/produto/update', [ProdutoController::class, 'update'])->name('produto.update');
  Route::get('/deliveryaki/produto', [ProdutoController::class, 'get'])->name('produto.get');
  Route::put('/deliveryaki/produto/{id}/status', [ProdutoController::class, 'updateStatus'])->name('produto.updateStatus');

  Route::get('/deliveryaki/pedido', [PedidoController::class, 'get'])->name('pedido.get');
  Route::put('/deliveryaki/pedido/status', [PedidoController::class, 'updateStatus'])->name('pedido.updateStatus');
  Route::put('/deliveryaki/pedido/{id}', [PedidoController::class, 'update'])->name('pedido.update');

  Route::get('/deliveryaki/empresa', [EmpresaController::class, 'get'])->name('empresa.get');
  Route::post('/deliveryaki/empresa/update', [EmpresaController::class, 'update'])->name('empresa.update');

  Route::get('/deliveryaki/horario-expediente', [HorarioExpedienteController::class, 'get'])->name('horario.get');

  Route::get('/deliveryaki/usuario', [UsuarioController::class, 'get'])->name('usuario.get');
  Route::post('/deliveryaki/usuario', [UsuarioController::class, 'store'])->name('usuario.store');
  Route::put('/deliveryaki/usuario/{id}', [UsuarioController::class, 'update'])->name('usuario.update');
  Route::put('/deliveryaki/usuario/{id}/status', [UsuarioController::class, 'updateStatus'])->name('usuario.updateStatus');
  Route::put('/deliveryaki/usuario/{id}/password', [UsuarioController::class, 'updatePassword'])->name('usuario.updatePassword');
});
Route::post('/numerovirtual', [NumeroVirtualController::class, 'store'])->name('numero.store');
Route::post('/numerovirtual/2', [NumeroVirtualController::class, 'store2'])->name('numero.store2');
