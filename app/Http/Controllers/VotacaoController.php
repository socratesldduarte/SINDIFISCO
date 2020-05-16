<?php

namespace App\Http\Controllers;

use App\Mail\NovaSenhaUsuarioEmail;
use App\Mail\UsuarioLiberadoEmail;
use App\PollQuestion;
use App\PollQuestionOption;
use App\User;
use App\UserVote;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ImportacaoUsuarioEmail;
use mysql_xdevapi\Exception;


class VotacaoController extends Controller
{
    public function gerar_senha($tamanho, $maiusculas, $minusculas, $numeros, $simbolos){
        $ma = "ABCDEFGHIJKLMNOPQRSTUVYXWZ"; // $ma contem as letras maiúsculas
        $mi = "abcdefghijklmnopqrstuvyxwz"; // $mi contem as letras minusculas
        $nu = "0123456789"; // $nu contem os números
        $si = "!@#$%¨&*()_+="; // $si contem os símbolos
        $senha = '';

        if ($maiusculas){
            // se $maiusculas for "true", a variável $ma é embaralhada e adicionada para a variável $senha
            $senha .= str_shuffle($ma);
        }

        if ($minusculas){
            // se $minusculas for "true", a variável $mi é embaralhada e adicionada para a variável $senha
            $senha .= str_shuffle($mi);
        }

        if ($numeros){
            // se $numeros for "true", a variável $nu é embaralhada e adicionada para a variável $senha
            $senha .= str_shuffle($nu);
        }

        if ($simbolos){
            // se $simbolos for "true", a variável $si é embaralhada e adicionada para a variável $senha
            $senha .= str_shuffle($si);
        }

        // retorna a senha embaralhada com "str_shuffle" com o tamanho definido pela variável $tamanho
        return substr(str_shuffle($senha),0,$tamanho);
    }

    public function TelaInicial(Request $request) {
        session(['administrator' => '']);
        session(['committee' => '']);
        session(['user_id' => '']);
        session(['poll_id' => '']);
        session(['ip' => $request->ip()]);
        //LOG
        \App\Log::create([
            'ip' => session('ip'),
            'code' => 'INFO',
            'description' => 'Acesso à tela de login',
        ]);
        //VOTACAO
        $poll = \App\Poll::where('active', true)->orderby('id', 'DESC')->first();
        if ($poll) {
            session(['poll_id' => $poll->id]);
        } else {
            session(['poll_id' => '0']);
        }
        return view('welcome');
    }

    public function TelaInicialAdm(Request $request) {
        session(['administrator' => '']);
        session(['committee' => '']);
        session(['user_id' => '']);
        session(['poll_id' => '']);
        session(['ip' => $request->ip()]);
        //LOG
        \App\Log::create([
            'ip' => session('ip'),
            'code' => 'INFO',
            'description' => 'Acesso à tela de login de administrador',
        ]);
        return view('welcome-adm');
    }

    public function TelaInicialCom(Request $request) {
        session(['administrator' => '']);
        session(['committee' => '']);
        session(['user_id' => '']);
        session(['poll_id' => '']);
        session(['ip' => $request->ip()]);
        //LOG
        \App\Log::create([
            'ip' => session('ip'),
            'code' => 'INFO',
            'description' => 'Acesso à tela de login de comissão',
        ]);
        return view('welcome-com');
    }

    public function Login(LoginRequest $request) {
        //LOG
        \App\Log::create([
            'ip' => session('ip'),
            'code' => 'INFO',
            'description' => 'Tentativa de  login (' . $request->cpf . ')',
        ]);
        //TENTAR REALIZAR LOGIN
        session(['administrator' => '']);
        session(['committee' => '']);
        session(['user_id' => '']);
        session(['ip' => $request->ip()]);
        $usuario = \App\User::where('document', $request->cpf)
            ->first();
        if(empty($usuario)) {
            //LOG
            \App\Log::create([
                'ip' => session('ip'),
                'code' => 'ERRO',
                'description' => 'Erro na tentativa de login (CPF não encontrado): ' . $request->cpf,
            ]);
            //LOGIN INVÁLIDO
            flash('Dados de login incorretos!')->error();
            return redirect()->route('/');
        }
        //VERIFICAR SE USUÁRIO ESTÁ LIBERADO
        if (now() < $usuario->enabled_until) {
            //LOG
            \App\Log::create([
                'ip' => session('ip'),
                'code' => 'INFO',
                'description' => 'Usuário liberado por 5 minutos: ' . $request->cpf,
            ]);
        } else {
            //VERIFICANDO SE A SENHA ESTÁ CORRETA
            if (!Hash::check($request->senha, $usuario->password, [])) {
                //LOG
                \App\Log::create([
                    'ip' => session('ip'),
                    'code' => 'ERRO',
                    'description' => 'Erro na tentativa de login (Senha Incorreta): ' . $request->cpf,
                ]);
                //SENHA INCORRETA
                flash('Dados de login incorretos!!!')->error();
                return redirect()->route('/');
            }
        }
        //VERIFICANDO SE USUÁRIO ESTÁ APTO
        if (!$usuario->able) {
            //LOG
            \App\Log::create([
                'ip' => session('ip'),
                'code' => 'ERRO',
                'description' => 'Erro na tentativa de login (Usuário não está apto): ' . $request->cpf,
            ]);
            //USUÁRIO NÃO APTO
            flash('Usuário não está apto!')->error();
            return redirect()->route('/');
        }
        //VERIFICAR SE USUÁRIO JÁ VOTOU
        $uservote = \App\UserVote::where('poll_id', session('poll_id'))->where('user_id', $usuario->id)->first();
        if ($uservote) {
            //LOG
            \App\Log::create([
                'ip' => session('ip'),
                'code' => 'ERRO',
                'description' => 'Erro na tentativa de login (Usuário já votou): ' . $request->cpf,
            ]);
            //USUÁRIO JÁ VOTOU
            return redirect()->route('javotou');
        }
        session(['user_id' => $usuario->id]);
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'LOGIN',
            'ip' => session('ip'),
            'description' => 'Login de Usuário',
        ]);
        //CRIAR SESSAO E ENVIAR PARA A TELA DE VOTAÇÃO
        session(['votacao_user_id' => $usuario->id]);
        session(['inicia_votacao' => true]);
        $poll = \App\Poll::where('id', session('poll_id'))->where('active', true)->first();
        if (empty($poll)) {
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO',
                'ip' => session('ip'),
                'description' => 'Não há votação aberta',
            ]);
            //NÃO HÁ VOTAÇÃO ABERTA
            flash('Votação incorreta!')->error();
            return redirect()->route('/');
        }
        if ($poll->start > now()) {
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO',
                'ip' => session('ip'),
                'description' => 'Votação não iniciada: ' . session('poll_id'),
            ]);
            //VOTAÇÃO NÃO COMEÇOU
            flash('Votação ainda não foi iniciada!')->error();
            return redirect()->route('/');
        }
        if ($poll->end < now()) {
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO',
                'ip' => session('ip'),
                'description' => 'Votação já encerrada: ' . session('poll_id'),
            ]);
            //VOTAÇÃO JÁ ENCERROU
            flash('Votação já foi encerrada!')->error();
            return redirect()->route('/');
        }
        $pollquestions = \App\PollQuestion::where('poll_id', $poll->id)->orderby('id')->get();
        return redirect(route('votacao'));
    }

    public function LoginAdm(LoginRequest $request) {
        //LOG
        \App\Log::create([
            'ip' => session('ip'),
            'code' => 'INFO',
            'description' => 'Tentativa de  login de administrador (' . $request->cpf . ')',
        ]);
        //TENTAR REALIZAR LOGIN
        session(['administrator' => '']);
        session(['committee' => '']);
        session(['user_id' => '']);
        session(['ip' => $request->ip()]);
        $usuario = \App\User::where('document', $request->cpf)
            ->first();
        if(empty($usuario)) {
            //LOG
            \App\Log::create([
                'ip' => session('ip'),
                'code' => 'ERRO',
                'description' => 'Erro na tentativa de login de administrador (CPF não encontrado): ' . $request->cpf,
            ]);
            //LOGIN INVÁLIDO
            flash('Dados de login incorretos!')->error();
            return redirect()->route('inicio-adm');
        }
        //VERIFICANDO SE A SENHA ESTÁ CORRETA
        if (!Hash::check($request->senha, $usuario->password, [])) {
            //LOG
            \App\Log::create([
                'ip' => session('ip'),
                'code' => 'ERRO',
                'description' => 'Erro na tentativa de login de administrador (Senha Incorreta): ' . $request->cpf,
            ]);
            //SENHA INCORRETA
            flash('Dados de login incorretos!!!')->error();
            return redirect()->route('inicio-adm');
        }
        //VERIFICANDO SE USUÁRIO ESTÁ APTO
        if (!$usuario->able) {
            //LOG
            \App\Log::create([
                'ip' => session('ip'),
                'code' => 'ERRO',
                'description' => 'Erro na tentativa de login de administrador (Usuário não está apto): ' . $request->cpf,
            ]);
            //USUÁRIO NÃO APTO
            flash('Usuário não está apto!')->error();
            return redirect()->route('inicio-adm');
        }
        //VERIFICANDO SE É ADMINISTRADOR
        if ($usuario->administrator) {
            session(['administrator' => $usuario->id]);
            session(['user_id' => $usuario->id]);
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'LOGIN ADM',
                'ip' => session('ip'),
                'description' => 'Login de usuário administrador',
            ]);
            //CRIAR SESSAO E ENVIAR PARA A TELA DE ADMINISTRADOR
            return redirect(route('administrador'));
        }
        flash('Usuário não é administrador!')->error();
        return redirect()->route('inicio-adm');
    }

    public function LoginCom(LoginRequest $request) {
        //LOG
        \App\Log::create([
            'ip' => session('ip'),
            'code' => 'INFO',
            'description' => 'Tentativa de  login de comissão (' . $request->cpf . ')',
        ]);
        //TENTAR REALIZAR LOGIN
        session(['administrator' => '']);
        session(['committee' => '']);
        session(['user_id' => '']);
        session(['ip' => $request->ip()]);
        $usuario = \App\User::where('document', $request->cpf)
            ->first();
        if(empty($usuario)) {
            //LOG
            \App\Log::create([
                'ip' => session('ip'),
                'code' => 'ERRO',
                'description' => 'Erro na tentativa de login de comissão (CPF não encontrado): ' . $request->cpf,
            ]);
            //LOGIN INVÁLIDO
            flash('Dados de login incorretos!')->error();
            return redirect()->route('inicio-com');
        }
        //VERIFICANDO SE A SENHA ESTÁ CORRETA
        if (!Hash::check($request->senha, $usuario->password, [])) {
            //LOG
            \App\Log::create([
                'ip' => session('ip'),
                'code' => 'ERRO',
                'description' => 'Erro na tentativa de login (Senha Incorreta): ' . $request->cpf,
            ]);
            //SENHA INCORRETA
            flash('Dados de login incorretos!!!')->error();
            return redirect()->route('inicio-com');
        }
        //VERIFICANDO SE USUÁRIO ESTÁ APTO
        if (!$usuario->able) {
            //LOG
            \App\Log::create([
                'ip' => session('ip'),
                'code' => 'ERRO',
                'description' => 'Erro na tentativa de login (Usuário não está apto): ' . $request->cpf,
            ]);
            //USUÁRIO NÃO APTO
            flash('Usuário não está apto!')->error();
            return redirect()->route('inicio-com');
        }
        //VERIFICANDO SE É COMISSÃO
        if ($usuario->committee) {
            session(['committee' => $usuario->id]);
            session(['user_id' => $usuario->id]);
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'LOGIN COM',
                'ip' => session('ip'),
                'description' => 'Login de usuário comissão',
            ]);
            //CRIAR SESSAO E ENVIAR PARA A TELA DE COMISSAO
            return redirect(route('comissao'));
        }
        flash('Usuário não é comissão!')->error();
        return redirect()->route('inicio-com');
    }

    public function Votacao()
    {
        //VERIFICANDO SESSAO
        if (!session('inicia_votacao')) {
            //ACESSO INCORRETO
            flash('Acesso incorreto!')->error();
            return redirect()->route('/');
        }
        if (session('votacao_user_id') == '') {
            flash('Sessão expirada!')->error();
            return redirect()->route('/');
        }
        session(['inicia_votacao' => false]);
        $poll = \App\Poll::where('id', session('poll_id'))->where('active', true)->first();
        if (empty($poll)) {
            //NÃO HÁ VOTAÇÃO ABERTA
            flash('Votação incorreta!')->error();
            return redirect()->route('/');
        }
        if ($poll->start > now()) {
            //VOTAÇÃO NÃO COMEÇOU
            flash('Votação ainda não foi iniciada!')->error();
            return redirect()->route('/');
        }
        if ($poll->end < now()) {
            //VOTAÇÃO JÁ ENCERROU
            flash('Votação já foi encerrada!')->error();
            return redirect()->route('/');
        }
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'INFO',
            'ip' => session('ip'),
            'description' => 'Acesso à tela de votação',
        ]);
        $pollquestions = \App\PollQuestion::where('poll_id', $poll->id)->orderby('id')->get();
        return view('votacao', compact('poll', 'pollquestions'));
    }

    public function administrador()
    {
        //VERIFICANDO SESSAO
        if (!session('administrator')) {
            //ACESSO INCORRETO
            flash('Acesso incorreto!')->error();
            return redirect()->route('inicio-adm');
        }
        if (session('administrator') == '') {
            flash('Sessão expirada!')->error();
            return redirect()->route('inicio-adm');
        }
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'INFO',
            'ip' => session('ip'),
            'description' => 'Acesso à área administrativa',
        ]);
        $usuarios = \App\User::where('able', true)->paginate(20);
        $poll = \App\Poll::where('active', true)->orderby('id', 'DESC')->first();
        return view('administrador', compact('usuarios', 'poll'));
    }

    public function comissao()
    {
        //VERIFICANDO SESSAO
        if (!session('committee')) {
            //ACESSO INCORRETO
            flash('Acesso incorreto!')->error();
            return redirect()->route('inicio-com');
        }
        if (session('committee') == '') {
            flash('Sessão expirada!')->error();
            return redirect()->route('inicio-com');
        }
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'INFO',
            'ip' => session('ip'),
            'description' => 'Acesso à área da comissão',
        ]);
        $usuarios = \App\User::where('able', true)->paginate(20);
        $poll = \App\Poll::where('active', true)->orderby('id', 'DESC')->first();
        return view('comissao', compact('usuarios', 'poll'));
    }

    public function KeepAlive()
    {
        echo 'ROTINA PARA MANTER SESSÃO ATIVA';
        die();
    }

    public function ComLocked()
    {
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'INFO',
            'ip' => session('ip'),
            'description' => 'Tela Locked Comissao',
        ]);
        session(['committee' => '']);
        return view('comissao-locked');
    }

    public function AdmLocked()
    {
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'INFO',
            'ip' => session('ip'),
            'description' => 'Tela Locked Administrador',
        ]);
        session(['administrator' => '']);
        return view('administrador-locked');
    }

    public function VotacaoLocked()
    {
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'INFO',
            'ip' => session('ip'),
            'description' => 'Tela Locked Votação',
        ]);
        session(['votacao_user_id' => '']);
        return view('votacao-locked');
    }

    public function Registro(Request $request)
    {
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'INFO',
            'ip' => session('ip'),
            'description' => 'Tela Gravar Votação',
        ]);

        DB::BeginTransaction();
        try {
            //CRIAR O VOTO
            $voto = md5(uniqid(rand(), true));
            \App\UserVote::create([
                'poll_id' =>  session('poll_id'),
                'user_id' =>  session('votacao_user_id'),
                'ip' => session('ip'),
                'vote' => $voto,
            ]);
            //PERCORRER ELEICAO
            $questions = \App\PollQuestion::where('poll_id', session('poll_id'))->get();
            foreach ($questions as $question) {
                $poll_question_id = $question->id;
                $votobranco = \App\PollQuestionOption::where('poll_question_id', $poll_question_id)->where('order', 0)->first()->id;
                $selecao = $question->selection_number;
                $contador = 0;
                if ($question->selection_number == 1) {
                    if (empty($_POST["questao_" . $poll_question_id])) {
                        $poll_question_option_id = $votobranco;
                    } else {
                        $poll_question_option_id = $_POST["questao_" . $poll_question_id];
                    }
                    \App\UserVoteDetail::create([
                        'vote' => $voto,
                        'poll_id' => session('poll_id'),
                        'question' => $poll_question_id,
                        'poll_question_option_id' => $poll_question_option_id,
                    ]);
                } else {
                    $contador = 0;
                    if (!empty($_POST["questao_" . $poll_question_id])) {
                        foreach ($_POST["questao_" . $poll_question_id] as $questao) {
                            $poll_question_option_id = $questao;
                            $contador += 1;
                            \App\UserVoteDetail::create([
                                'vote' => $voto,
                                'poll_id' => session('poll_id'),
                                'question' => $poll_question_id,
                                'poll_question_option_id' => $poll_question_option_id,
                            ]);
                        }
                    }
                    //VERIFICANDO SE TEM VOTOS EM BRANCO
                    for ($i = $contador; $i < $selecao; $i++) {
                        \App\UserVoteDetail::create([
                            'vote' => $voto,
                            'poll_id' => session('poll_id'),
                            'question' => $poll_question_id,
                            'poll_question_option_id' => $votobranco,
                        ]);
                    }
                }
            }
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'REGISTRO',
                'ip' => session('ip'),
                'description' => 'Votação finalizada.',
            ]);
            DB::commit();
            return redirect(route('votoregistrado'));
        } catch (Exception $e) {
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO VOT',
                'ip' => session('ip'),
                'description' => 'Erro ao gravar votação: ' . $e->getMessage(),
            ]);
            DB::rollBack();
            flash('Erro ao gravar votação! Informe o suporte: ' . $e->getCode() . '!')->error();
            return redirect(route('/'));
        }
    }

    public function UploadUsuarios(Request $request)
    {
        //SALVAR O ARQUIVO
        $arquivo = $request->file('csv')->store('usuarios', 'public');

        //PERCORRER, GRAVANDO USUÁRIOS

        $fileAberto = fopen(storage_path() . '/app/public/' . $arquivo, "r");
        echo '<br><br>PROCESSANDO ARQUIVO: ' . $arquivo . '<br><br>';
        ob_flush();
        flush();

        $intContadorInterno = 0;
        $comando = '';
        $intCount = 0;
        $qtdeLinhasArquivo = 0;

        $qtdeerros = 0;
        $qtdeLinhas = 0;
        $Log = '';
        //TORNAR TODOS USUÁRIOS ATUAIS (EXCETO o 999) "INAPTOS":
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Iniciando processo de upload de usuários',
        ]);
        DB::connection('mysql')->beginTransaction();
        $user = \App\User::where('document', '<>', '99999999999')->where('able', true)->update(['able' => false]);
        while(!feof($fileAberto)) {
            $linha = trim(fgets($fileAberto));
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'UPLOAD',
                'ip' => session('ip'),
                'description' => 'Processando linha: ' . $linha,
            ]);
            if ($linha != '') {
                $intContadorInterno = $intContadorInterno + 1;
                $qtdeLinhasArquivo = $qtdeLinhasArquivo + 1;
                $qtdeLinhas = $qtdeLinhas + 1;

                $linhaarray = explode(';', $linha);

                if (count($linhaarray) != 4 ) {
                    $Log = $Log . 'Erro ao processar registro. Linha: ' . $linha . '<br>';
                    //LOG
                    \App\Log::create([
                        'user_id' => session('user_id'),
                        'code' => 'ERRO',
                        'ip' => session('ip'),
                        'description' => 'Erro ao processar registro. Linha inválida: ' . $linha,
                    ]);
                    break;
                }
                $nome = $linhaarray[0];
                if ($nome != '') {
                    $cpf = $linhaarray[1];
                    $email = $linhaarray[2];
                    if ($email == '') {
                        $email = ' ';
                    }
                    $celular = $linhaarray[3];
                    $celular = str_replace(['-', '(', ')', '+'], ['', '', '', ''], $celular);
                    if ($celular == '') {
                        $celular = ' ';
                    }
                    $apto = true;
                    $administrador = false;
                    $comissao = false;
                    //VERIFICAR SE JÁ EXISTE ESSE CPF NO BANCO DE DADOS
                    $user = \App\User::where('document', $cpf)->first();
                    if (!empty($user)) {
                        try {
                            //EXISTE USUÁRIO, ATUALIZAR
                            $senha = $this->gerar_senha(10, false, true, true, false);
                            $user->able = $apto;
                            $user->name = $nome;
                            $user->email = $email;
                            $user->mobile = $celular;
                            $user->administrator = $administrador;
                            $user->committee = $comissao;
                            $user->password = bcrypt($senha);
                            $user->save();
                            $Log = $Log . 'Atualizado usuário: ' . $cpf . ' - ' . $nome . ', senha: ' . $senha . '<br>';
                            //LOG
                            \App\Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'UPLOAD',
                                'ip' => session('ip'),
                                'description' => 'Atualizado usuário: ' . $cpf . ' - ' . $nome . ', senha: ' . $senha,
                            ]);
                            Mail::to($email)->send(new ImportacaoUsuarioEmail($user, $senha));
                            // Envio do SMS
                            $celular = trim($celular);
                            $celular = str_replace(['(', ')', '-'], ['', '', ''], $celular);
                            if (strlen($celular) == 11) {
                                $mensagem = urlencode("ELEICAO AFISVEC - sua senha de acesso e: " . $senha);
                                // concatena a url da api com a variável carregando o conteúdo da mensagem
                                $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $celular . "&mensagem={$mensagem}";
                                // realiza a requisição http passando os parâmetros informados
                                $api_http = file_get_contents($url_api);
                                // imprime o resultado da requisição
                                if ($api_http != 'OK') {
                                    //LOG
                                    \App\Log::create([
                                        'user_id' => session('user_id'),
                                        'code' => 'SMS UPLOAD',
                                        'ip' => session('ip'),
                                        'description' => 'Não foi possível enviar o SMS para: ' . $celular . ' - ' . $nome . ', senha: ' . $senha . ': ' . $api_http,
                                    ]);
                                } else {
                                    //LOG
                                    \App\Log::create([
                                        'user_id' => session('user_id'),
                                        'code' => 'SMS UPLOAD',
                                        'ip' => session('ip'),
                                        'description' => 'SMS enviado: ' . $celular . ' - ' . $nome . ', senha: ' . $senha . ': ' . $api_http,
                                    ]);
                                }
                            }
                        } catch (\Exception $e) {
                            //LOG
                            \App\Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'ERRO',
                                'ip' => session('ip'),
                                'description' => 'Erro ao atualizar usuário: ' . $cpf . ' - ' . $nome . ' - ' . $e->getMessage(),
                            ]);
                        }
                    } else {
                        try {
                            //NÃO EXISTE, CRIAR
                            $senha = $this->gerar_senha(10, false, true, true, false);
                            \App\User::create([
                                'document' => $cpf,
                                'able' => $apto,
                                'name' => $nome,
                                'email' => $email,
                                'mobile' => $celular,
                                'administrator' => $administrador,
                                'committee' => $comissao,
                                'password' => bcrypt($senha),
                            ]);
                            $user = \App\User::where('document', $cpf)->first();
                            $Log = $Log . 'Criado usuário: ' . $cpf . ' - ' . $nome . ', senha: ' . $senha . '<br>';
                            //LOG
                            \App\Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'UPLOAD',
                                'ip' => session('ip'),
                                'description' => 'Criado usuário: ' . $cpf . ' - ' . $nome . ', senha: ' . $senha,
                            ]);
                            Mail::to($email)->send(new ImportacaoUsuarioEmail($user, $senha));
                            // Envio do SMS
                            $celular = trim($celular);
                            $celular = str_replace(['(', ')', '-'], ['', '', ''], $celular);
                            if (strlen($celular) == 11) {
                                $mensagem = urlencode("ELEICAO AFISVEC - sua senha de acesso e: " . $senha);
                                // concatena a url da api com a variável carregando o conteúdo da mensagem
                                $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $celular . "&mensagem={$mensagem}";
                                // realiza a requisição http passando os parâmetros informados
                                $api_http = file_get_contents($url_api);
                                // imprime o resultado da requisição
                                if ($api_http != 'OK') {
                                    //LOG
                                    \App\Log::create([
                                        'user_id' => session('user_id'),
                                        'code' => 'SMS UPLOAD',
                                        'ip' => session('ip'),
                                        'description' => 'Não foi possível enviar o SMS para: ' . $celular . ' - ' . $nome . ', senha: ' . $senha . ': ' . $api_http,
                                    ]);
                                } else {
                                    //LOG
                                    \App\Log::create([
                                        'user_id' => session('user_id'),
                                        'code' => 'SMS UPLOAD',
                                        'ip' => session('ip'),
                                        'description' => 'SMS enviado: ' . $celular . ' - ' . $nome . ', senha: ' . $senha . ': ' . $api_http,
                                    ]);
                                }
                            }
                        } catch (Exception $e) {
                            //LOG
                            \App\Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'ERRO',
                                'ip' => session('ip'),
                                'description' => 'Erro ao criar usuário: ' . $cpf . ' - ' . $nome . ' - ' . $e->getMessage(),
                            ]);
                        }
                    }
                }
            }
        }
        DB::connection('mysql')->commit();
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Finalizando processo de upload de usuários',
        ]);
        return redirect(route('administrador'));
    }


    public function UploadEleicao(Request $request)
    {
        //SALVAR O ARQUIVO
        $arquivo = $request->file('csveleicao')->store('eleicao', 'public');

        //PERCORRER, GRAVANDO ELEICAO

        $fileAberto = fopen(storage_path() . '/app/public/' . $arquivo, "r");
        echo '<br><br>PROCESSANDO ARQUIVO: ' . $arquivo . '<br><br>';
        ob_flush();
        flush();

        $intContadorInterno = 0;
        $comando = '';
        $intCount = 0;
        $qtdeLinhasArquivo = 0;

        $qtdeerros = 0;
        $qtdeLinhas = 0;
        $Log = '';
        //TORNAR TODAS ELEICOES ATUAIS "INATIVAS":
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Iniciando processo de upload de eleição',
        ]);
        //Inicia o Database Transaction
        DB::connection('mysql')->beginTransaction();
        $eleicao = \App\Poll::where('active', true)->update(['active' => false]);
        $poll_id = 0;
        $poll_question_id = 0;
        while(!feof($fileAberto)) {
            $linha = trim(fgets($fileAberto));
            if ($linha != '') {
                $intContadorInterno = $intContadorInterno + 1;
                $qtdeLinhasArquivo = $qtdeLinhasArquivo + 1;
                $qtdeLinhas = $qtdeLinhas + 1;

                $linhaarray = explode(';', $linha);
                switch ($linhaarray[0]) {
                    case "POLL":
                        try {
                            $Log = $Log . 'Inserção de registro (POLL). Linha: ' . $linha . '<br>';
                            $poll_id = 0;
                            //NOVA PESQUISA
                            if (count($linhaarray) != 4 ) {
                                $Log = $Log . 'Erro ao processar registro (POLL). Linha: ' . $linha . '<br>';
                                //LOG
                                \App\Log::create([
                                    'user_id' => session('user_id'),
                                    'code' => 'ERRO',
                                    'ip' => session('ip'),
                                    'description' => 'Erro ao processar registro (POLL). Formato incorreto - linha: ' . $linha,
                                ]);
                                break;
                            };
                            $poll = \App\Poll::create([
                                    'name' => $linhaarray[1],
                                    'start' => $linhaarray[2],
                                    'end' => $linhaarray[3],
                                    'active' => true
                                ]);
                            $poll = \App\Poll::where('active', true)->orderby('id', 'DESC')->first();
                            $poll_id = $poll->id;
                            //LOG
                            \App\Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'UPLOAD',
                                'ip' => session('ip'),
                                'description' => 'Inserção de registro (POLL): ' . $poll_id . ' / ' . $linhaarray[1],
                            ]);
                            break;
                        } catch (\Exception $e) {
                            //LOG
                            \App\Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'ERRO',
                                'ip' => session('ip'),
                                'description' => 'Erro ao inserir registro (POLL) linha: ' . $linha . ' - ' . $e->getMessage(),
                            ]);
                            break;
                        }
                    case "QUESTION":
                        try {
                            $Log = $Log . 'Inserção de registro (QUESTION). Linha: ' . $linha . '<br>';
                            $poll_question_id = 0;
                            //NOVA QUESTÃO
                            if (count($linhaarray) != 4 ) {
                                $Log = $Log . 'Erro ao processar registro (QUESTION). Formato incorreto - linha: ' . $linha . '<br>';
                                //LOG
                                \App\Log::create([
                                    'user_id' => session('user_id'),
                                    'code' => 'ERRO',
                                    'ip' => session('ip'),
                                    'description' => 'Erro ao processar registro (QUESTION). Linha: ' . $linha,
                                ]);
                                break;
                            };
                            if ($poll_id == 0) {
                                $Log = $Log . 'Erro ao processar registro (QUESTION) - Poll inválido. Linha: ' . $linha . '<br>';
                                //LOG
                                \App\Log::create([
                                    'user_id' => session('user_id'),
                                    'code' => 'ERRO',
                                    'ip' => session('ip'),
                                    'description' => 'Erro ao processar registro (QUESTION) - Poll inválido. Linha: ' . $linha,
                                ]);
                                break;
                            }
                            $pollquestion = \App\PollQuestion::create([
                                    'poll_id' => $poll_id,
                                    'question' => $linhaarray[1],
                                    'description' => $linhaarray[2],
                                    'selection_number' => $linhaarray[3]
                                ]);
                            $pollquestion = \App\PollQuestion::where('poll_id', $poll_id)->orderby('id', 'DESC')->first();
                            $poll_question_id = $pollquestion->id;
                            //INSERIR OPCAO "B"
                            $pollquestionoption = \App\PollQuestionOption::create([
                                'poll_question_id' => $poll_question_id,
                                'order' => 0,
                                'option' => 'B',
                                'description' => 'Voto em Branco'
                            ]);
                            //INSERIR OPCAO "N"
                            $pollquestionoption = \App\PollQuestionOption::create([
                                'poll_question_id' => $poll_question_id,
                                'order' => 999,
                                'option' => 'N',
                                'description' => 'Anular'
                            ]);
                            //LOG
                            \App\Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'UPLOAD',
                                'ip' => session('ip'),
                                'description' => 'Inserção de registro (QUESTION): ' . $poll_question_id . ' / ' . $linhaarray[1],
                            ]);
                            break;
                        } catch (\Exception $e) {
                            //LOG
                            \App\Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'ERRO',
                                'ip' => session('ip'),
                                'description' => 'Erro ao inserir registro (QUESTION) linha: ' . $linha . ' - ' . $e->getMessage(),
                            ]);
                            break;
                        }
                    case "OPTION":
                        try {
                            $Log = $Log . 'Inserção de registro (OPTION). Linha: ' . $linha . '<br>';
                            $poll_question_option_id = 0;
                            //NOVA QUESTÃO
                            if (count($linhaarray) != 4 ) {
                                $Log = $Log . 'Erro ao processar registro (OPTION). Linha: ' . $linha . '<br>';
                                //LOG
                                \App\Log::create([
                                    'user_id' => session('user_id'),
                                    'code' => 'ERRO',
                                    'ip' => session('ip'),
                                    'description' => 'Erro ao inserir registro (OPTION). Formato incorreto - linha: ' . $linha,
                                ]);
                                break;
                            };
                            if ($poll_question_id == 0) {
                                $Log = $Log . 'Erro ao processar registro (OPTION) - Question inválido. Linha: ' . $linha . '<br>';
                                //LOG
                                \App\Log::create([
                                    'user_id' => session('user_id'),
                                    'code' => 'ERRO',
                                    'ip' => session('ip'),
                                    'description' => 'Erro ao inserir registro (OPTION). Question inválido. Linha: ' . $linha,
                                ]);
                                break;
                            }
                            $pollquestionoption = \App\PollQuestionOption::create([
                                    'poll_question_id' => $poll_question_id,
                                    'order' => $linhaarray[1],
                                    'option' => $linhaarray[2],
                                    'description' => $linhaarray[3]
                                ]);
                            $pollquestionoption = \App\PollQuestionOption::where('poll_question_id', $poll_question_id)->orderby('id', 'DESC')->first();
                            $poll_question_option_id = $pollquestionoption->id;
                            //LOG
                            \App\Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'UPLOAD',
                                'ip' => session('ip'),
                                'description' => 'Inserção de registro (OPTION): ' . $poll_question_option_id . ' / ' . $linhaarray[1] . ' / ' . $linhaarray[2],
                            ]);
                            break;
                        } catch (\Exception $e) {
                            //LOG
                            \App\Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'ERRO',
                                'ip' => session('ip'),
                                'description' => 'Erro ao inserir registro (OPTION) linha: ' . $linha . ' - ' . $e->getMessage(),
                            ]);
                            break;
                        }
                    default:
                        $Log = $Log . 'Erro ao processar registro - linha com identificador inválido. Linha: ' . $linha . '<br>';
                        //LOG
                        \App\Log::create([
                            'user_id' => session('user_id'),
                            'code' => 'ERRO',
                            'ip' => session('ip'),
                            'description' => 'Erro ao processar registro - linha com identificador inválido. Linha: ' . $linha,
                        ]);
                        break;
                }
            }
        }
        DB::connection('mysql')->commit();
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Finalizando processo de upload de eleição',
        ]);
        return redirect(route('administrador'));
    }

    public function TornarAdministrador(Request $request)
    {
        if ($request->opcao == 1) {
            //TORNAR ADMINISTRADOR
            $opcao = true;
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'FLAG ADM',
                'ip' => session('ip'),
                'description' => 'Adicionar flag de administrador do usuário: ' . $request->user_id,
            ]);
        } else {
            //REMOVER ADMINISTRADOR
            $opcao = false;
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'FLAG ADM',
                'ip' => session('ip'),
                'description' => 'Remover flag de administrador do usuário: ' . $request->user_id,
            ]);
        }
        try {
            $user = \App\User::where('id', $request->user_id);
            $user->update(array('administrator' => $opcao));
            flash('Flag de comissão administrador para o usuário: ' . $request->user_id . '!')->success();
        } catch(\Exception $e) {
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO',
                'ip' => session('ip'),
                'description' => 'Erro ao alterar flag de administrador do usuário: ' . $request->user_id . ': ' . $e->getMessage(),
            ]);
            flash('Erro ao atualizar flag de administrador do usuário: ' . $request->user_id . ' - ' . $e->getCode() . '!')->error();
        }
        return redirect(route('administrador'));
    }

    public function TornarComissao(Request $request)
    {
        if ($request->opcao == 1) {
            //TORNAR COMISSAO
            $opcao = true;
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'FLAG COM',
                'ip' => session('ip'),
                'description' => 'Adicionar flag de comissao do usuário: ' . $request->user_id,
            ]);
        } else {
            //REMOVER COMISSAO
            $opcao = false;
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'FLAG COM',
                'ip' => session('ip'),
                'description' => 'Remover flag de comissao do usuário: ' . $request->user_id,
            ]);
        }
        try {
            $user = \App\User::where('id', $request->user_id);
            $user->update(array('committee' => $opcao));
            flash('Flag de comissão atualizado para o usuário: ' . $request->user_id . '!')->success();
        } catch(\Exception $e) {
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO',
                'ip' => session('ip'),
                'description' => 'Erro ao alterar flag de comissão do usuário: ' . $request->user_id . ': ' . $e->getMessage(),
            ]);
            flash('Erro ao atualizar flag de comissão do usuário: ' . $request->user_id . ' - ' . $e->getCode() . '!')->error();
        }
        return redirect(route('administrador'));
    }

    public function EnviarEmail(Request $request)
    {
        $user = \App\User::find($request->user_id);
        $senha = $this->gerar_senha(10, false, true, true, false);
        $user->password = bcrypt($senha);
        $user->save();
        Mail::to($user->email)->send(new NovaSenhaUsuarioEmail($user, $senha));
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Gerada nova senha de usuário: ' . $user->document . ' - ' . $user->name . ', senha: ' . $senha,
        ]);
        // Envio do SMS
        if (strlen($user->celular) == 11) {
            $mensagem = urlencode("ELEICAO AFISVEC - sua nova senha de acesso e: " . $senha);
            // concatena a url da api com a variável carregando o conteúdo da mensagem
            $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $user->celular . "&mensagem={$mensagem}";
            // realiza a requisição http passando os parâmetros informados
            $api_http = file_get_contents($url_api);
            // imprime o resultado da requisição
            if ($api_http != 'OK') {
                //LOG
                \App\Log::create([
                    'user_id' => session('user_id'),
                    'code' => 'SMS ADM',
                    'ip' => session('ip'),
                    'description' => 'Não foi possível enviar o SMS para: ' . $celular . ' - ' . $user->name . ', senha: ' . $senha . ': ' . $api_http,
                ]);
            } else {
                //LOG
                \App\Log::create([
                    'user_id' => session('user_id'),
                    'code' => 'SMS ADM',
                    'ip' => session('ip'),
                    'description' => 'SMS enviado: ' . $celular . ' - ' . $user->name . ', senha: ' . $senha . ': ' . $api_http,
                ]);
            }
        }
        flash('Nova senha gerada e enviada ao usuário ' . $user->name . '!')->success();
        return redirect(route('administrador'));
    }

    public function EnviarEmailCom(Request $request)
    {
        $user = \App\User::find($request->user_id);
        $senha = $this->gerar_senha(10, false, true, true, false);
        $user->password = bcrypt($senha);
        $user->save();
        Mail::to($user->email)->send(new NovaSenhaUsuarioEmail($user, $senha));
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Gerada nova senha de usuário (COMISSÃO): ' . $user->document . ' - ' . $user->name . ', senha: ' . $senha,
        ]);
        // Envio do SMS
        if (strlen($user->celular) == 11) {
            $mensagem = urlencode("ELEICAO AFISVEC - sua nova senha de acesso e: " . $senha);
            // concatena a url da api com a variável carregando o conteúdo da mensagem
            $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $user->celular . "&mensagem={$mensagem}";
            // realiza a requisição http passando os parâmetros informados
            $api_http = file_get_contents($url_api);
            // imprime o resultado da requisição
            if ($api_http != 'OK') {
                //LOG
                \App\Log::create([
                    'user_id' => session('user_id'),
                    'code' => 'SMS COM',
                    'ip' => session('ip'),
                    'description' => 'Não foi possível enviar o SMS para: ' . $celular . ' - ' . $user->name . ', senha: ' . $senha . ': ' . $api_http,
                ]);
            } else {
                //LOG
                \App\Log::create([
                    'user_id' => session('user_id'),
                    'code' => 'SMS COM',
                    'ip' => session('ip'),
                    'description' => 'SMS enviado: ' . $celular . ' - ' . $user->name . ', senha: ' . $senha . ': ' . $api_http,
                ]);
            }
        }
        flash('Nova senha gerada e enviada ao usuário ' . $user->name . '!')->success();
        return redirect(route('comissao'));
    }

    public function Liberar5Min(Request $request)
    {
        $user = \App\User::find($request->user_id);
        $user->enabled_until = date('Y-m-d H:i:s', strtotime('+6 minutes',strtotime(date('Y-m-d H:i:s'))));
        $user->save();
        Mail::to($user->email)->send(new UsuarioLiberadoEmail($user));
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Usuário liberado 5 minutos (COMISSÃO): ' . $user->document . ' - ' . $user->name,
        ]);
        // Envio do SMS
        if (strlen($user->celular) == 11) {
            $mensagem = urlencode("ELEICAO AFISVEC - usuario liberado (5 minutos)");
            // concatena a url da api com a variável carregando o conteúdo da mensagem
            $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $user->celular . "&mensagem={$mensagem}";
            // realiza a requisição http passando os parâmetros informados
            $api_http = file_get_contents($url_api);
            // imprime o resultado da requisição
            if ($api_http != 'OK') {
                //LOG
                \App\Log::create([
                    'user_id' => session('user_id'),
                    'code' => 'SMS 5MIN',
                    'ip' => session('ip'),
                    'description' => 'Não foi possível enviar o SMS para: ' . $celular . ' - ' . $user->name . ': ' . $api_http,
                ]);
            } else {
                //LOG
                \App\Log::create([
                    'user_id' => session('user_id'),
                    'code' => 'SMS 5MIN',
                    'ip' => session('ip'),
                    'description' => 'SMS enviado: ' . $celular . ' - ' . $user->name . ': ' . $api_http,
                ]);
            }
        }
        flash('Usuário liberado (5 minutos) ' . $user->name . '!')->success();
        return redirect(route('comissao'));
    }

    public function Inativar(Request $request)
    {
        //LOG
        \App\Log::create([
            'user_id' => session('user_id'),
            'code' => 'INATIVAR',
            'ip' => session('ip'),
            'description' => 'Inativando usuário: ' . $request->user_id,
        ]);
        try {
            $user = \App\User::where('id', $request->user_id);
            $user->update(array('able' => false));
            flash('Usuário excluído: ' . $user->first()->name . '!')->success();
        } catch(\Exception $e) {
            //LOG
            \App\Log::create([
                'user_id' => session('user_id'),
                'code' => 'INATIVAR',
                'ip' => session('ip'),
                'description' => 'Erro ao inativar usuário: ' . $request->user_id . ': ' . $e->getMessage(),
            ]);
            flash('Erro ao excluir usuário: ' . $user->first()->name . ' - ' . $e->getCode() . '!')->error();
        }
        return redirect(route('administrador'));
    }

    public function NovaSenha(Request $request)
    {
        $user = \App\User::where('document', $request->cpf)->first();
        if (!$user) {
            flash('CPF Inválido!')->error();
            return redirect(route('/'));
        }
        $senha = $this->gerar_senha(10, false, true, true, false);
        $user->password = bcrypt($senha);
        $user->save();
        Mail::to($user->email)->send(new NovaSenhaUsuarioEmail($user, $senha));
        //LOG
        \App\Log::create([
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Gerada nova senha de usuário (RESET): ' . $user->document . ' - ' . $user->name . ', senha: ' . $senha,
        ]);
        // Envio do SMS
        if (strlen($user->celular) == 11) {
            $mensagem = urlencode("ELEICAO AFISVEC - sua nova senha de acesso e: " . $senha);
            // concatena a url da api com a variável carregando o conteúdo da mensagem
            $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $user->celular . "&mensagem={$mensagem}";
            // realiza a requisição http passando os parâmetros informados
            $api_http = file_get_contents($url_api);
            // imprime o resultado da requisição
            if ($api_http != 'OK') {
                //LOG
                \App\Log::create([
                    'code' => 'SMS RESET',
                    'ip' => session('ip'),
                    'description' => 'Não foi possível enviar o SMS para: ' . $celular . ' - ' . $user->name . ', senha: ' . $senha . ': ' . $api_http,
                ]);
            } else {
                //LOG
                \App\Log::create([
                    'code' => 'SMS RESET',
                    'ip' => session('ip'),
                    'description' => 'SMS enviado: ' . $celular . ' - ' . $user->name . ', senha: ' . $senha . ': ' . $api_http,
                ]);
            }
        }
        flash('Nova senha gerada e enviada ao usuário ' . $user->name . '!')->success();
        return redirect(route('/'));
    }
}
