<?php

use App\Models\Poll;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{VotacaoController,AdminController};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', [VotacaoController::class, 'TelaInicial'])->name('/');
Route::get('/eleicao/{codigo}', [VotacaoController::class, 'TelaInicial']);
Route::get('/op/{codigo}', [VotacaoController::class, 'TelaInicial'])->name('eleicao.codigo');
Route::get('/inicio-adm', [VotacaoController::class, 'TelaInicialAdm'])->name('inicio-adm');
Route::get('/inicio-com', [VotacaoController::class, 'TelaInicialCom'])->name('inicio-com');

Route::get('/documentos', [AdminController::class, 'Documentos'])->name('documentos');
Route::get('/documentos/{poll_id}/zeresima', [AdminController::class, 'Zeresima'])->name('zeresima');
Route::get('/documentos/{poll_id}/boletimapuracao', [AdminController::class, 'BoletimApuracao'])->name('boletimapuracao');
Route::get('/documentos/{poll_id}/relatorio', [AdminController::class, 'Relatorio'])->name('relatorio');
Route::post('/autenticidade', [AdminController::class, 'Autenticidade'])->name('autenticidade');

Route::post('/novasenha', [VotacaoController::class, 'NovaSenha'])->name('novasenha');
Route::post('/login', [VotacaoController::class, 'Login'])->name('login');
Route::post('/login-administrador', [VotacaoController::class, 'LoginAdm'])->name('login-administrador');
Route::post('/login-comissao', [VotacaoController::class, 'LoginCom'])->name('login-comissao');

Route::get('/votacao', [VotacaoController::class, 'Votacao'])->name('votacao');
Route::get('/administrador', [VotacaoController::class, 'Administrador'])->name('administrador');
Route::get('/comissao', [VotacaoController::class, 'Comissao'])->name('comissao');

Route::post('/uploadusuarios', [VotacaoController::class, 'UploadUsuarios'])->name('uploadusuarios');
Route::post('/uploadusuariostemporarios', [VotacaoController::class, 'UploadTempUser'])->name('uploadusuariostemporarios');
Route::post('/uploadeleicao', [VotacaoController::class, 'UploadEleicao'])->name('uploadeleicao');

Route::post('/tornaradministrador', [VotacaoController::class, 'TornarAdministrador'])->name('tornaradministrador');
Route::post('/tornarcomissao', [VotacaoController::class, 'TornarComissao'])->name('tornarcomissao');
Route::post('/enviaremail', [VotacaoController::class, 'EnviarEmail'])->name('enviaremail');
Route::post('/inativar', [VotacaoController::class, 'Inativar'])->name('inativar');

Route::post('/enviaremailcom', [VotacaoController::class, 'EnviarEmailCom'])->name('enviaremailcom');
Route::post('/liberar5min', [VotacaoController::class, 'Liberar5Min'])->name('liberar5min');

Route::get('/keep-alive', [VotacaoController::class, 'KeepAlive'])->name('keep-alive');
Route::get('/adm-locked', [VotacaoController::class, 'AdmLocked'])->name('adm-locked');
Route::get('/com-locked', [VotacaoController::class, 'ComLocked'])->name('com-locked');
Route::get('/votacao-locked', [VotacaoController::class, 'VotacaoLocked'])->name('votacao-locked');
Route::post('/registrovoto', [VotacaoController::class, 'Registro'])->name('registrovoto');

Route::get('/votoregistrado', function () {
    $poll = Poll::find(session('poll_id'));
    return view('votoregistrado', compact('poll'));
})->name('votoregistrado');

Route::get('/javotou', function () {
    return view('javotou');
})->name('javotou');

Route::get('/zeresima', function () {
    return view('zeresima');
});

//Route::get('/importaTemp', [VotacaoController::class, 'ImportFromTemp'])->name('importaTemp');
