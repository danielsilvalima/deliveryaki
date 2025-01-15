<?php

use App\Http\Controllers\Empresa\AgendaEmpresaController;
use App\Http\Controllers\HorarioExpediente\AgendaHorarioExpedienteController;
use App\Http\Controllers\Servico\AgendaServicoController;
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
  Route::post('/agenda/empresa', [AgendaEmpresaController::class, 'store'])->name('agenda.store');
  Route::get('/agenda/horario-expediente', [AgendaHorarioExpedienteController::class, 'get'])->name('expediente.get');
  Route::get('/agenda/servico', [AgendaServicoController::class, 'get'])->name('servico.get');
});
