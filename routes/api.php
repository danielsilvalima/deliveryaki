<?php

use App\Http\Controllers\Empresa\AgendaEmpresaController;
use App\Http\Controllers\HorarioExpediente\AgendaHorarioExpedienteController;
use App\Http\Controllers\EmpresaExpediente\EmpresaExpedienteController;
use App\Http\Controllers\Servico\AgendaServicoController;
use App\Http\Controllers\Cliente\AgendaClienteController;
use App\Http\Controllers\Recurso\AgendaRecursoController;
use App\Http\Controllers\NumeroVirtual\NumeroVirtualController;
use App\Http\Controllers\Produto\StoreProdutoController;
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
  Route::get('/agenda/empresa/cliente/agenda', [AgendaClienteController::class, 'get'])->name('agenda.get');
  Route::post('/agenda/empresa/cliente/agenda', [AgendaClienteController::class, 'store'])->name('agenda.store');
  Route::delete('/agenda/empresa/cliente/agenda', [AgendaClienteController::class, 'destroy'])->name('agenda.destroy');


  //store
  Route::get('/store/produto/banner', [StoreProdutoController::class, 'getBanner'])->name('store.getBanner');

  Route::post('/store/produto', [StoreProdutoController::class, 'store'])->name('store.store');
  Route::delete('/store/produto/{id}', [StoreProdutoController::class, 'delete'])->name('store.delete');
  Route::post('/store/produto/clique', [StoreProdutoController::class, 'storeClique'])->name('store.storeClique');
});
Route::post('/numerovirtual', [NumeroVirtualController::class, 'store'])->name('numero.store');
Route::post('/numerovirtual/2', [NumeroVirtualController::class, 'store2'])->name('numero.store2');
Route::get('/store/produto', [StoreProdutoController::class, 'get'])->name('store.get');
Route::get('/store/produto/{id}', [StoreProdutoController::class, 'getByID'])->name('store.getByID');
