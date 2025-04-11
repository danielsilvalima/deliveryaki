<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\Categoria\CategoriaController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\Empresa\EmpresaController;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\MdiIcons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\Pedido\PedidoController;
use App\Http\Controllers\Produto\ProdutoController;
use App\Http\Controllers\tables\Basic as TablesBasic;
use App\Http\Controllers\Usuario\UsuarioController;
use App\Http\Controllers\Cardapio\CardapioController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Cep\CepController;
use App\Http\Controllers\EmpresaExpediente\EmpresaExpedienteController;

// Main Page Route

Route::get('/', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::post('/cep', [RegisterBasic::class, 'getCEP'])->name('auth-register-basic.getCEP');
//Route::get('/login', [LoginBasic::class, 'index'])->name('auth-login-basic');

//API FRONT
Route::middleware('api.keypedido')->group(function () {
  Route::get('/api/cardapio/{id}', [CardapioController::class, 'get'])->name('cardapio.get');
  Route::post('/api/pedido/{id}', [PedidoController::class, 'post'])->name('pedido.post');
  //Route::get('/api/cliente/{id}', [ClienteController::class, 'get'])->name('cliente.get');

  //Route::get('/api/cep', [CepController::class, 'get'])->name('cep.get');
});

//Route::get('/api/pedido', [PedidoController::class, 'post'])->name('pedido.post');

Route::middleware('auth:sanctum')->group(function () {
  //Route::get('/home', [Analytics::class, 'index'])->name('dashboard-analytics');
  Route::get('/home', [HomeController::class, 'index'])->name('dashboard-analytics');
  Route::post('/home', [HomeController::class, 'post'])->name('dashboard-analytics.post');

  Route::get('/auth/login-basic/login', [LoginBasic::class, 'logout'])->name('auth-login-basic-logout');

  //Empresa
  Route::get('/empresa', [EmpresaController::class, 'index'])->name('empresa.index');
  Route::get('/empresa/create', [EmpresaController::class, 'create'])->name('empresa.create');
  //Route::post('/empresa', [EmpresaController::class, 'store'])->name('empresa.store');
  Route::get('/empresa/{id}', [EmpresaController::class, 'show'])->name('empresa.show');
  Route::put('/empresa/{id}', [EmpresaController::class, 'edit'])->name('empresa.edit');
  Route::delete('/empresa/{id}/delete', [EmpresaController::class, 'modal'])->name('empresa.modal');
  Route::delete('/empresa/{id}', [EmpresaController::class, 'delete'])->name('empresa.delete');
  Route::delete('/empresa/{id}/remover-logo', [EmpresaController::class, 'deleteLogo'])->name('empresa.deleteLogo');

  //EmpresaExpediente
  Route::delete('/empresa-expediente/{id}', [EmpresaExpedienteController::class, 'destroy'])->name(
    'empresaexpediente.destroy'
  );

  //Produto
  Route::get('/produto', [ProdutoController::class, 'index'])->name('produto.index');
  Route::get('/produto/create', [ProdutoController::class, 'create'])->name('produto.create');
  //Route::post('/produto', [ProdutoController::class, 'store'])->name('produto.store');
  Route::get('/produto/{id}', [ProdutoController::class, 'show'])->name('produto.show');
  Route::put('/produto/{id}', [ProdutoController::class, 'edit'])->name('produto.edit');
  Route::delete('/produto/{id}/delete', [ProdutoController::class, 'modal'])->name('produto.modal');
  Route::delete('/produto/{id}', [ProdutoController::class, 'delete'])->name('produto.delete');
  Route::delete('/produto/{id}/remover-logo', [ProdutoController::class, 'deleteLogo'])->name('produto.deleteLogo');

  //Cliente
  //Route::get('/cliente', [ClienteController::class, 'index'])->name('cliente.index');
  Route::get('/cliente/create', [ClienteController::class, 'create'])->name('cliente.create');
  Route::post('/cliente', [ClienteController::class, 'store'])->name('cliente.store');
  Route::get('/cliente/{id}', [ClienteController::class, 'show'])->name('cliente.show');
  Route::put('/cliente/{id}', [ClienteController::class, 'edit'])->name('cliente.edit');
  Route::delete('/cliente/{id}/delete', [ClienteController::class, 'modal'])->name('cliente.modal');
  Route::delete('/cliente/{id}', [ClienteController::class, 'delete'])->name('cliente.delete');

  //Categoria
  Route::get('/categoria', [CategoriaController::class, 'index'])->name('categoria.index');
  Route::get('/categoria/create', [CategoriaController::class, 'create'])->name('categoria.create');
  //Route::post('/categoria', [CategoriaController::class, 'store'])->name('categoria.store');
  Route::get('/categoria/{id}', [CategoriaController::class, 'show'])->name('categoria.show');
  Route::put('/categoria/{id}', [CategoriaController::class, 'edit'])->name('categoria.edit');
  Route::delete('/categoria/{id}/delete', [CategoriaController::class, 'modal'])->name('categoria.modal');
  Route::delete('/categoria/{id}', [CategoriaController::class, 'delete'])->name('categoria.delete');

  //Pedido
  Route::get('/pedido', [PedidoController::class, 'index'])->name('pedido.index');
  Route::post('/pedido', [PedidoController::class, 'postPedido'])->name('pedido.postPedido');
  Route::get('/pedido/{id}', [PedidoController::class, 'show'])->name('pedido.show');
  //Route::put('/pedido/{id}', [PedidoController::class, 'updateStatus'])->name('pedido.updateStatus');
  //Route::put('/pedido/update/{id}', [PedidoController::class, 'update'])->name('pedido.update');

  //Usuário
  Route::get('/usuario/{id}', [UsuarioController::class, 'show'])->name('usuario.show');
});

// layout
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// pages
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name(
  'pages-account-settings-account'
);
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name(
  'pages-account-settings-notifications'
);
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name(
  'pages-account-settings-connections'
);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name(
  'pages-misc-under-maintenance'
);

// authentication
//Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::post('/auth/login-basic/login', [LoginBasic::class, 'login'])->name('auth-login-basic-login');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::post('/auth/register-basic/create', [RegisterBasic::class, 'store'])->name('auth-register-store');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');

// cards
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

// User Interface
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// extended ui
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

// icons
Route::get('/icons/icons-mdi', [MdiIcons::class, 'index'])->name('icons-mdi');

// form elements
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// form layouts
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

// tables
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');
