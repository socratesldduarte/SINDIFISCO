<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/', 'VotacaoController@TelaInicial')->name('/');
Route::get('/inicio-adm', 'VotacaoController@TelaInicialAdm')->name('inicio-adm');
Route::get('/inicio-com', 'VotacaoController@TelaInicialCom')->name('inicio-com');

Route::get('/documentos', 'AdminController@Documentos')->name('documentos');
Route::get('/documentos/{poll_id}/zeresima', 'AdminController@Zeresima')->name('zeresima');
Route::get('/documentos/{poll_id}/boletimapuracao', 'AdminController@BoletimApuracao')->name('boletimapuracao');
Route::post('/autenticidade', 'AdminController@Autenticidade')->name('autenticidade');

Route::post('/novasenha', 'VotacaoController@NovaSenha')->name('novasenha');
Route::post('/login', 'VotacaoController@Login')->name('login');
Route::post('/login-administrador', 'VotacaoController@LoginAdm')->name('login-administrador');
Route::post('/login-comissao', 'VotacaoController@LoginCom')->name('login-comissao');

Route::get('/votacao', 'VotacaoController@Votacao')->name('votacao');
Route::get('/administrador', 'VotacaoController@Administrador')->name('administrador');
Route::get('/comissao', 'VotacaoController@Comissao')->name('comissao');

Route::post('/uploadusuarios', 'VotacaoController@UploadUsuarios')->name('uploadusuarios');
Route::post('/uploadeleicao', 'VotacaoController@UploadEleicao')->name('uploadeleicao');

Route::post('/tornaradministrador', 'VotacaoController@TornarAdministrador')->name('tornaradministrador');
Route::post('/tornarcomissao', 'VotacaoController@TornarComissao')->name('tornarcomissao');
Route::post('/enviaremail', 'VotacaoController@EnviarEmail')->name('enviaremail');
Route::post('/inativar', 'VotacaoController@Inativar')->name('inativar');

Route::post('/enviaremailcom', 'VotacaoController@EnviarEmailCom')->name('enviaremailcom');
Route::post('/liberar5min', 'VotacaoController@Liberar5Min')->name('liberar5min');

Route::get('/keep-alive', 'VotacaoController@KeepAlive')->name('keep-alive');
Route::get('/adm-locked', 'VotacaoController@AdmLocked')->name('adm-locked');
Route::get('/com-locked', 'VotacaoController@ComLocked')->name('com-locked');
Route::get('/votacao-locked', 'VotacaoController@VotacaoLocked')->name('votacao-locked');
Route::post('/registrovoto', 'VotacaoController@Registro')->name('registrovoto');

Route::get('/votoregistrado', function () {
    return view('votoregistrado');
})->name('votoregistrado');

Route::get('/javotou', function () {
    return view('javotou');
})->name('javotou');

Route::get('/zeresima', function () {
    return view('zeresima');
});
