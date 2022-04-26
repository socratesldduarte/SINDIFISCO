<?php

namespace App\Http\Controllers;

use App\Mail\NovaSenhaUsuarioEmail;
use App\Mail\ReminderUsuarioEmail;
use App\Mail\UsuarioLiberadoEmail;
use App\Models\Poll;
use App\Models\PollQuestion;
use App\Models\PollQuestionOption;
use App\Models\TempUser;
use App\Models\User;
use App\Models\UserVote;
use App\Models\UserVoteDetail;
use App\Models\Log;
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

    public function TelaInicial(string $codigo = null, Request $request) {
        $polls = Poll::where('active', true)->orderby('id')->get();
        session(['administrator' => '']);
        session(['committee' => '']);
        session(['user_id' => '']);
        session(['poll_id' => '']);
        session(['ip' => $request->ip()]);
        //LOG
        Log::create([
            'ip' => session('ip'),
            'code' => 'INFO',
            'description' => 'Acesso à tela de login - ' . $codigo,
        ]);
        $poll = Poll::where('code', $codigo)->first();
        if ($poll) {
            session(['poll_id' => $poll->id]);
        } else {
            session(['poll_id' => '0']);
        }
        if (count($polls) === 1) {
            $poll = $polls->first();
            session(['poll_id' => $poll->id]);
        }
        //VOTACAO
        return view('welcome', compact('poll', 'polls'));
    }

    public function TelaInicialAdm(Request $request) {
        session(['administrator' => '']);
        session(['committee' => '']);
        session(['user_id' => '']);
        session(['poll_id' => '']);
        session(['ip' => $request->ip()]);
        //LOG
        Log::create([
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
        Log::create([
            'ip' => session('ip'),
            'code' => 'INFO',
            'description' => 'Acesso à tela de login de comissão',
        ]);
        return view('welcome-com');
    }

    public function Login(LoginRequest $request) {
        //LOG
        Log::create([
            'ip' => session('ip'),
            'code' => 'INFO',
            'description' => 'Tentativa de  login (' . $request->cpf . ')',
        ]);
        //TENTAR REALIZAR LOGIN
        session(['administrator' => '']);
        session(['committee' => '']);
        session(['user_id' => '']);
        session(['ip' => $request->ip()]);
        $usuario = User::where('document', $request->cpf);
        if (session('poll_id') != 0) {
            $usuario = $usuario->where('poll_id', session('poll_id'));
        }
        $usuario = $usuario->first();
        if(empty($usuario)) {
            //LOG
            Log::create([
                'ip' => session('ip'),
                'code' => 'ERRO',
                'description' => 'Erro na tentativa de login (CPF não encontrado): ' . $request->cpf,
            ]);
            //LOGIN INVÁLIDO
            flash('Dados de login incorretos ou Eleição não selecionada!')->error();
            return redirect(request()->headers->get('referer'))->with('error', 'login incorreto ou eleição não selecionada');
        }
        //VERIFICAR SE USUÁRIO ESTÁ LIBERADO
        if (now() < $usuario->enabled_until) {
            //LOG
            Log::create([
                'ip' => session('ip'),
                'code' => 'INFO',
                'description' => 'Usuário liberado por 5 minutos: ' . $request->cpf,
            ]);
        } else {
            //VERIFICANDO SE A SENHA ESTÁ CORRETA
            if (!Hash::check($request->senha, $usuario->password, [])) {
                //LOG
                Log::create([
                    'ip' => session('ip'),
                    'code' => 'ERRO',
                    'description' => 'Erro na tentativa de login (Senha Incorreta): ' . $request->cpf,
                ]);
                //SENHA INCORRETA
                flash('Dados de login incorretos!!!')->error();
                return redirect(request()->headers->get('referer'))->with('error', 'login incorreto');
            }
        }
        //VERIFICANDO SE USUÁRIO ESTÁ APTO
        if (!$usuario->able) {
            //LOG
            Log::create([
                'ip' => session('ip'),
                'code' => 'ERRO',
                'description' => 'Erro na tentativa de login (Usuário não está apto): ' . $request->cpf,
            ]);
            //USUÁRIO NÃO APTO
            flash('Usuário não está apto!')->error();
            return redirect(request()->headers->get('referer'))->with('error', 'login incorreto');
        }
        //VERIFICAR SE USUÁRIO JÁ VOTOU
        $uservote = UserVote::where('poll_id', session('poll_id'))->where('user_id', $usuario->id)->first();
        if ($uservote) {
            //LOG
            Log::create([
                'ip' => session('ip'),
                'code' => 'ERRO',
                'description' => 'Erro na tentativa de login (Usuário já votou): ' . $request->cpf,
            ]);
            //USUÁRIO JÁ VOTOU
            return redirect()->route('javotou');
        }
        session(['user_id' => $usuario->id]);
        //LOG
        Log::create([
            'user_id' => session('user_id'),
            'code' => 'LOGIN',
            'ip' => session('ip'),
            'description' => 'Login de Usuário',
        ]);
        //CRIAR SESSAO E ENVIAR PARA A TELA DE VOTAÇÃO
        session(['votacao_user_id' => $usuario->id]);
        session(['inicia_votacao' => true]);
        $poll = Poll::where('id', session('poll_id'))->where('active', true)->first();
        if (empty($poll)) {
            //LOG
            Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO',
                'ip' => session('ip'),
                'description' => 'Não há votação aberta',
            ]);
            //NÃO HÁ VOTAÇÃO ABERTA
            flash('Votação incorreta!')->error();
            return redirect(request()->headers->get('referer'))->with('error', 'votação incorreta');
        }
        if ($poll->start > now()) {
            //LOG
            Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO',
                'ip' => session('ip'),
                'description' => 'Votação não iniciada: ' . session('poll_id'),
            ]);
            //VOTAÇÃO NÃO COMEÇOU
            flash('Votação ainda não foi iniciada!')->error();
            return redirect(request()->headers->get('referer'))->with('error', 'votação não iniciada');
        }
        if ($poll->end < now()) {
            //LOG
            Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO',
                'ip' => session('ip'),
                'description' => 'Votação já encerrada: ' . session('poll_id'),
            ]);
            //VOTAÇÃO JÁ ENCERROU
            flash('Votação já foi encerrada!')->error();
            return redirect(request()->headers->get('referer'))->with('error', 'votação encerrada');
        }
        return redirect(route('votacao'));
    }

    public function LoginAdm(LoginRequest $request) {
        //LOG
        Log::create([
            'ip' => session('ip'),
            'code' => 'INFO',
            'description' => 'Tentativa de  login de administrador (' . $request->cpf . ')',
        ]);
        //TENTAR REALIZAR LOGIN
        session(['administrator' => '']);
        session(['committee' => '']);
        session(['user_id' => '']);
        session(['ip' => $request->ip()]);
        $usuario = User::where('document', $request->cpf)
            ->first();
        if(empty($usuario)) {
            //LOG
            Log::create([
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
            Log::create([
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
            Log::create([
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
            Log::create([
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
        Log::create([
            'ip' => session('ip'),
            'code' => 'INFO',
            'description' => 'Tentativa de  login de comissão (' . $request->cpf . ')',
        ]);
        //TENTAR REALIZAR LOGIN
        session(['administrator' => '']);
        session(['committee' => '']);
        session(['user_id' => '']);
        session(['ip' => $request->ip()]);
        $usuario = User::where('document', $request->cpf)
            ->first();
        if(empty($usuario)) {
            //LOG
            Log::create([
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
            Log::create([
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
            Log::create([
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
            Log::create([
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
            return redirect(request()->headers->get('referer'))->with('error', 'Votação incorreta');
        }
        if (session('votacao_user_id') == '') {
            flash('Sessão expirada!')->error();
            return redirect(request()->headers->get('referer'))->with('error', 'Votação incorreta');
        }
        session(['inicia_votacao' => false]);
        $poll = Poll::where('id', session('poll_id'))->where('active', true)->first();
        if (empty($poll)) {
            //NÃO HÁ VOTAÇÃO ABERTA
            flash('Votação incorreta!')->error();
            return redirect(request()->headers->get('referer'))->with('error', 'Votação incorreta');
        }
        if ($poll->start > now()) {
            //VOTAÇÃO NÃO COMEÇOU
            flash('Votação ainda não foi iniciada!')->error();
            return redirect(request()->headers->get('referer'))->with('error', 'Votação incorreta');
        }
        if ($poll->end < now()) {
            //VOTAÇÃO JÁ ENCERROU
            flash('Votação já foi encerrada!')->error();
            return redirect(request()->headers->get('referer'))->with('error', 'Votação incorreta');
        }
        //LOG
        Log::create([
            'user_id' => session('user_id'),
            'code' => 'INFO',
            'ip' => session('ip'),
            'description' => 'Acesso à tela de votação',
        ]);
        $user = User::find(session('votacao_user_id'));
        $pollquestions = PollQuestion::where('poll_id', $poll->id)->orderby('id')->get();
        return view('votacao', compact('poll', 'user'));
    }

    public function administrador(Request $request)
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
        Log::create([
            'user_id' => session('user_id'),
            'code' => 'INFO',
            'ip' => session('ip'),
            'description' => 'Acesso à área administrativa',
        ]);
        if ($request->key <> '') {
            $usuarios = User::where('name', 'LIKE', '%' . trim($request->key) . '%')->orderby('poll_id', 'ASC')->paginate(20);
        } else {
            $usuarios = User::orderby('poll_id', 'ASC')->paginate(20);
        }
        $polls = Poll::with('pollquestions')->where('active', true)->get();
        return view('administrador', compact('usuarios', 'polls'));
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
        Log::create([
            'user_id' => session('user_id'),
            'code' => 'INFO',
            'ip' => session('ip'),
            'description' => 'Acesso à área da comissão',
        ]);
        $usuarios = User::where('able', true)->paginate(20);
        $poll = Poll::where('active', true)->orderby('id', 'DESC')->first();
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
        Log::create([
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
        Log::create([
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
        Log::create([
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
        Log::create([
            'user_id' => session('user_id'),
            'code' => 'INFO',
            'ip' => session('ip'),
            'description' => 'Tela Gravar Votação',
        ]);

        DB::BeginTransaction();
        try {
            //CRIAR O VOTO
            $voto = md5(uniqid(rand(), true));
            UserVote::create([
                'poll_id' =>  session('poll_id'),
                'user_id' =>  session('votacao_user_id'),
                'ip' => session('ip'),
                'vote' => $voto,
            ]);
            //PERCORRER ELEICAO
            $questions = PollQuestion::where('poll_id', session('poll_id'))->get();
            foreach ($questions as $question) {
                $poll_question_id = $question->id;
                $votobranco = PollQuestionOption::where('poll_question_id', $poll_question_id)->where('order', 0)->first()->id;
                $selecao = $question->selection_number;
                $contador = 0;
                if ($question->selection_number == 1) {
                    if (empty($_POST["questao_" . $poll_question_id])) {
                        $poll_question_option_id = $votobranco;
                    } else {
                        $poll_question_option_id = $_POST["questao_" . $poll_question_id];
                    }
                    UserVoteDetail::create([
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
                            UserVoteDetail::create([
                                'vote' => $voto,
                                'poll_id' => session('poll_id'),
                                'question' => $poll_question_id,
                                'poll_question_option_id' => $poll_question_option_id,
                            ]);
                        }
                    }
                    //VERIFICANDO SE TEM VOTOS EM BRANCO
                    for ($i = $contador; $i < $selecao; $i++) {
                        UserVoteDetail::create([
                            'vote' => $voto,
                            'poll_id' => session('poll_id'),
                            'question' => $poll_question_id,
                            'poll_question_option_id' => $votobranco,
                        ]);
                    }
                }
            }
            //LOG
            Log::create([
                'user_id' => session('user_id'),
                'code' => 'REGISTRO',
                'ip' => session('ip'),
                'description' => 'Votação finalizada.',
            ]);
            DB::commit();
            return redirect(route('votoregistrado'));
        } catch (Exception $e) {
            //LOG
            Log::create([
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
        //LOG
        Log::create([
            'user_id' => session('user_id'),
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Iniciando processo de upload de usuários',
        ]);
        while(!feof($fileAberto)) {
            $linha = trim(fgets($fileAberto));
            //LOG
            Log::create([
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

                if (count($linhaarray) != 9 ) {
                    $Log = $Log . 'Erro ao processar registro. Linha: ' . $linha . '<br>';
                    //LOG
                    Log::create([
                        'user_id' => session('user_id'),
                        'code' => 'ERRO',
                        'ip' => session('ip'),
                        'description' => 'Erro ao processar registro. Linha inválida: ' . $linha,
                    ]);
                    break;
                }
                $nome = $linhaarray[4];
                if ($nome != '') {
                    $cpf = $linhaarray[5];
                    $email = $linhaarray[6];
                    $birthday = $linhaarray[7];
                    if ($email == '') {
                        $email = ' ';
                    }
                    $celular = $linhaarray[8];
                    $celular = str_replace(['-', '(', ')', '+'], ['', '', '', ''], $celular);
                    if ($celular == '') {
                        $celular = ' ';
                    }
                    $apto = true;
                    $mesa = $linhaarray[0];
                    $poll = Poll::where('code', $mesa)->first();
                    $linhaarray[1] == '1' ? $administrador = true : $administrador = false;
                    $linhaarray[2] == '1' ? $comissao = true : $comissao = false;
                    $linhaarray[3] == '1' ? $can_be_voted = true : $can_be_voted = false;
                    //VERIFICAR SE JÁ EXISTE ESSE CPF NO BANCO DE DADOS
                    $user = User::where('document', $cpf);
                    if ($poll) {
                        $user = $user->where('poll_id', $poll->id);
                    }
                    $user = $user->first();
                    if (!empty($user)) {
                        try {
                            DB::connection('mysql')->beginTransaction();
                            //EXISTE USUÁRIO NA MESA, ATUALIZAR
                            $senha = $this->gerar_senha(5, false, true, true, false);
                            $user->able = $apto;
                            $user->name = $nome;
                            $user->email = $email;
                            $user->birthday = $birthday;
                            $user->mobile = $celular;
                            $user->administrator = $administrador;
                            $user->committee = $comissao;
                            $user->can_be_voted = $can_be_voted;
                            $user->password = bcrypt($senha);
                            $user->save();
                            $Log = $Log . 'Atualizado usuário: ' . $cpf . ' - ' . $nome . ', senha: ' . $senha . '<br>';
                            //LOG
                            Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'UPLOAD',
                                'ip' => session('ip'),
                                'description' => 'Atualizado na mesa ' . $mesa . ' o usuário: ' . $cpf . ' - ' . $nome . ', senha: ' . $senha,
                            ]);
//echo 'USUÁRIO: ' . $cpf . ', senha: ' . $senha . '<br>';
                            try {
                                Mail::to($email)->send(new ImportacaoUsuarioEmail($user, $senha));
                            } catch (\Exception $e) {
                                Log::create([
                                    'user_id' => session('user_id'),
                                    'code' => 'ERRO',
                                    'ip' => session('ip'),
                                    'description' => 'Erro ao enviar e-mail: ' . $e->getMessage(),
                                ]);
                            }
                            // Envio do SMS
                            $celular = $user->mobile;
                            $celular = trim($celular);
                            $celular = str_replace(['+55'], [''], $celular);
                            $celular = str_replace(['(', ')', '-'], ['', '', ''], $celular);
                            if (strlen($celular) == 11) {
                                $mesa = '';
                                if ($user->poll) {
                                    $mesa = $user->poll->code;
                                }
                                $mensagem = urlencode("VOTAÇÕES SINDIFISCOP-RS - endereço " . asset('/') . "op/" . $mesa . " sua senha de acesso e: " . $senha);
                                // concatena a url da api com a variável carregando o conteúdo da mensagem
                                $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $celular . "&mensagem={$mensagem}";
                                // realiza a requisição http passando os parâmetros informados
                                $api_http = file_get_contents($url_api);
                                // imprime o resultado da requisição
                                if ($api_http != 'OK') {
                                    //LOG
                                    Log::create([
                                        'user_id' => session('user_id'),
                                        'code' => 'SMS UPLOAD',
                                        'ip' => session('ip'),
                                        'description' => 'Não foi possível enviar o SMS para: ' . $celular . ' - ' . $nome . ', senha: ' . $senha . ': ' . $api_http,
                                    ]);
                                } else {
                                    //LOG
                                    Log::create([
                                        'user_id' => session('user_id'),
                                        'code' => 'SMS UPLOAD',
                                        'ip' => session('ip'),
                                        'description' => 'SMS enviado: ' . $celular . ' - ' . $nome . ', senha: ' . $senha . ': ' . $api_http,
                                    ]);
                                }
                            }
                            DB::connection('mysql')->commit();
                        } catch (\Exception $e) {
                            DB::connection('mysql')->rollBack();
                            //LOG
                            Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'ERRO',
                                'ip' => session('ip'),
                                'description' => 'Erro ao atualizar usuário: ' . $cpf . ' - ' . $nome . ' - ' . $e->getMessage(),
                            ]);
                        }
                    } else {
                        try {
                            DB::connection('mysql')->beginTransaction();
                            //NÃO EXISTE, CRIAR
                            $senha = $this->gerar_senha(5, false, true, true, false);
                            $user = User::create([
                                'poll_id' => $poll ? $poll->id : null,
                                'document' => $cpf,
                                'able' => $apto,
                                'name' => $nome,
                                'email' => $email,
                                'birthday' => $birthday,
                                'mobile' => $celular,
                                'administrator' => $administrador,
                                'committee' => $comissao,
                                'can_be_voted' => $can_be_voted,
                                'password' => bcrypt($senha),
                            ]);
                            $Log = $Log . 'Criado usuário: ' . $cpf . ' - ' . $nome . ', senha: ' . $senha . '<br>';
                            //LOG
                            Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'UPLOAD',
                                'ip' => session('ip'),
                                'description' => 'Criado usuário: ' . $cpf . ' - ' . $nome . ', senha: ' . $senha,
                            ]);
//echo 'USUÁRIO: ' . $cpf . ', senha: ' . $senha . '<br>';
                            try {
                                Mail::to($email)->send(new ImportacaoUsuarioEmail($user, $senha));
                            } catch (\Exception $e) {
                                Log::create([
                                    'user_id' => session('user_id'),
                                    'code' => 'ERRO',
                                    'ip' => session('ip'),
                                    'description' => 'Erro ao enviar e-mail: ' . $e->getMessage(),
                                ]);
                            }
                            // Envio do SMS
                            $celular = $user->mobile;
                            $celular = trim($celular);
                            $celular = str_replace(['+55'], [''], $celular);
                            $celular = str_replace(['(', ')', '-'], ['', '', ''], $celular);
                            if (strlen($celular) == 11) {
                                $mesa = '';
                                if ($user->poll) {
                                    $mesa = $user->poll->code;
                                }
                                $mensagem = urlencode("VOTAÇÕES SINDIFISCO-RS - endereço " . asset('/') . "op/" . $mesa . " sua senha de acesso e: " . $senha);
                                // concatena a url da api com a variável carregando o conteúdo da mensagem
                                $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $celular . "&mensagem={$mensagem}";
                                // realiza a requisição http passando os parâmetros informados
                                $api_http = file_get_contents($url_api);
                                // imprime o resultado da requisição
                                if ($api_http != 'OK') {
                                    //LOG
                                    Log::create([
                                        'user_id' => session('user_id'),
                                        'code' => 'SMS UPLOAD',
                                        'ip' => session('ip'),
                                        'description' => 'Não foi possível enviar o SMS para: ' . $celular . ' - ' . $nome . ', senha: ' . $senha . ': ' . $api_http,
                                    ]);
                                } else {
                                    //LOG
                                    Log::create([
                                        'user_id' => session('user_id'),
                                        'code' => 'SMS UPLOAD',
                                        'ip' => session('ip'),
                                        'description' => 'SMS enviado: ' . $celular . ' - ' . $nome . ', senha: ' . $senha . ': ' . $api_http,
                                    ]);
                                }
                            }
                            DB::connection('mysql')->commit();
                        } catch (Exception $e) {
                            DB::connection('mysql')->rollBack();
                            //LOG
                            Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'ERRO',
                                'ip' => session('ip'),
                                'description' => 'Erro ao criar usuário: ' . $cpf . ' - ' . $nome . ' - ' . $e->getMessage(),
                            ]);
                        }
                    }
                    if ($can_be_voted && $poll) {
                        if ($poll->poll_type_id == 2) {
                            //ADICIONAR O USUÁRIO COMO CANDIDATO
                            $pollquestion = $poll->pollquestions()->first();
                            if ($pollquestion) {
                                $option = $pollquestion->pollquestionoptions()->where('description', $nome)->first();
                                if (!$option) {
                                    //CREATE
                                    $lastoption = $pollquestion->pollquestionoptions()
                                        ->where('order', '<>', '0')
                                        ->where('order', '<>', '999')
                                        ->orderby('order', 'DESC')
                                        ->first();
                                    $order = 1;
                                    if ($lastoption) {
                                        $order = (int)$lastoption->order + 1;
                                    }
                                    $candidato = $pollquestion->pollquestionoptions()->create(
                                        [
                                            'poll_question_id' => $pollquestion->id,
                                            'order' => $order,
                                            'option' => $order,
                                            'description' => $nome,
                                        ]
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        DB::connection('mysql')->commit();
        //LOG
        Log::create([
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
        Log::create([
            'user_id' => session('user_id'),
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Iniciando processo de upload de eleição',
        ]);
        //Inicia o Database Transaction
        DB::connection('mysql')->beginTransaction();
        $eleicao = Poll::where('active', true)->update(['active' => false]);
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
                            if (count($linhaarray) != 6 ) {
                                $Log = $Log . 'Erro ao processar registro (POLL). Linha: ' . $linha . '<br>';
                                //LOG
                                Log::create([
                                    'user_id' => session('user_id'),
                                    'code' => 'ERRO',
                                    'ip' => session('ip'),
                                    'description' => 'Erro ao processar registro (POLL). Formato incorreto - linha: ' . $linha,
                                ]);
                                break;
                            };
                            $poll = Poll::create([
                                    'poll_type_id' => $linhaarray[1],
                                    'code' => $linhaarray[2],
                                    'name' => $linhaarray[3],
                                    'start' => $linhaarray[4],
                                    'end' => $linhaarray[5],
                                    'active' => true
                                ]);
                            $poll_id = $poll->id;
                            //LOG
                            Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'UPLOAD',
                                'ip' => session('ip'),
                                'description' => 'Inserção de registro (POLL): ' . $poll_id . ' / ' . $linhaarray[2],
                            ]);
                            break;
                        } catch (\Exception $e) {
                            //LOG
                            Log::create([
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
                                Log::create([
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
                                Log::create([
                                    'user_id' => session('user_id'),
                                    'code' => 'ERRO',
                                    'ip' => session('ip'),
                                    'description' => 'Erro ao processar registro (QUESTION) - Poll inválido. Linha: ' . $linha,
                                ]);
                                break;
                            }
                            $pollquestion = PollQuestion::create([
                                    'poll_id' => $poll_id,
                                    'question' => $linhaarray[1],
                                    'description' => $linhaarray[2],
                                    'selection_number' => $linhaarray[3]
                                ]);
                            $pollquestion = PollQuestion::where('poll_id', $poll_id)->orderby('id', 'DESC')->first();
                            $poll_question_id = $pollquestion->id;
                            //INSERIR OPCAO "B"
                            $pollquestionoption = PollQuestionOption::create([
                                'poll_question_id' => $poll_question_id,
                                'order' => 0,
                                'option' => 'B',
                                'description' => 'Voto em Branco'
                            ]);
                            //INSERIR OPCAO "N"
                            $pollquestionoption = PollQuestionOption::create([
                                'poll_question_id' => $poll_question_id,
                                'order' => 999,
                                'option' => 'N',
                                'description' => 'Anular'
                            ]);
                            //LOG
                            Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'UPLOAD',
                                'ip' => session('ip'),
                                'description' => 'Inserção de registro (QUESTION): ' . $poll_question_id . ' / ' . $linhaarray[1],
                            ]);
                            break;
                        } catch (\Exception $e) {
                            //LOG
                            Log::create([
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
                                Log::create([
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
                                Log::create([
                                    'user_id' => session('user_id'),
                                    'code' => 'ERRO',
                                    'ip' => session('ip'),
                                    'description' => 'Erro ao inserir registro (OPTION). Question inválido. Linha: ' . $linha,
                                ]);
                                break;
                            }
                            $pollquestionoption = PollQuestionOption::create([
                                    'poll_question_id' => $poll_question_id,
                                    'order' => $linhaarray[1],
                                    'option' => $linhaarray[2],
                                    'description' => $linhaarray[3]
                                ]);
                            $pollquestionoption = PollQuestionOption::where('poll_question_id', $poll_question_id)->orderby('id', 'DESC')->first();
                            $poll_question_option_id = $pollquestionoption->id;
                            //LOG
                            Log::create([
                                'user_id' => session('user_id'),
                                'code' => 'UPLOAD',
                                'ip' => session('ip'),
                                'description' => 'Inserção de registro (OPTION): ' . $poll_question_option_id . ' / ' . $linhaarray[1] . ' / ' . $linhaarray[2],
                            ]);
                            break;
                        } catch (\Exception $e) {
                            //LOG
                            Log::create([
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
                        Log::create([
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
        Log::create([
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
            Log::create([
                'user_id' => session('user_id'),
                'code' => 'FLAG ADM',
                'ip' => session('ip'),
                'description' => 'Adicionar flag de administrador do usuário: ' . $request->user_id,
            ]);
        } else {
            //REMOVER ADMINISTRADOR
            $opcao = false;
            //LOG
            Log::create([
                'user_id' => session('user_id'),
                'code' => 'FLAG ADM',
                'ip' => session('ip'),
                'description' => 'Remover flag de administrador do usuário: ' . $request->user_id,
            ]);
        }
        try {
            $user = User::where('id', $request->user_id);
            $user->update(array('administrator' => $opcao));
            flash('Flag de comissão administrador para o usuário: ' . $request->user_id . '!')->success();
        } catch(\Exception $e) {
            //LOG
            Log::create([
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
            Log::create([
                'user_id' => session('user_id'),
                'code' => 'FLAG COM',
                'ip' => session('ip'),
                'description' => 'Adicionar flag de comissao do usuário: ' . $request->user_id,
            ]);
        } else {
            //REMOVER COMISSAO
            $opcao = false;
            //LOG
            Log::create([
                'user_id' => session('user_id'),
                'code' => 'FLAG COM',
                'ip' => session('ip'),
                'description' => 'Remover flag de comissao do usuário: ' . $request->user_id,
            ]);
        }
        try {
            $user = User::where('id', $request->user_id);
            $user->update(array('committee' => $opcao));
            flash('Flag de comissão atualizado para o usuário: ' . $request->user_id . '!')->success();
        } catch(\Exception $e) {
            //LOG
            Log::create([
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
        $user = User::find($request->user_id);
        $senha = $this->gerar_senha(5, false, true, true, false);
        $user->password = bcrypt($senha);
        $user->save();
        //LOG
        Log::create([
            'user_id' => session('user_id'),
            'code' => 'ADM RESET',
            'ip' => session('ip'),
            'description' => 'Gerada nova senha de usuário: ' . $user->document . ' - ' . $user->name . ', senha: ' . $senha,
        ]);
        try {
            Mail::to($user->email)->send(new NovaSenhaUsuarioEmail($user, $senha));
        } catch (\Exception $e) {
            Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO',
                'ip' => session('ip'),
                'description' => 'Erro ao enviar e-mail: ' . $e->getMessage(),
            ]);
        }
        // Envio do SMS
        $celular = $user->mobile;
        $celular = trim($celular);
        $celular = str_replace(['+55'], [''], $celular);
        $celular = str_replace(['(', ')', '-'], ['', '', ''], $celular);
        if (strlen($celular) == 11) {
            $mesa = '';
            if ($user->poll) {
                $mesa = $user->poll->code;
            }
            $mensagem = urlencode("VOTAÇÕES SINDIFISCO-RS - endereço " . asset('/') . "op/" . $mesa . " sua nova senha de acesso e: " . $senha);
            // concatena a url da api com a variável carregando o conteúdo da mensagem
            $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $celular . "&mensagem={$mensagem}";
            // realiza a requisição http passando os parâmetros informados
            $api_http = file_get_contents($url_api);
            // imprime o resultado da requisição
            if ($api_http != 'OK') {
                //LOG
                Log::create([
                    'user_id' => session('user_id'),
                    'code' => 'SMS ADM',
                    'ip' => session('ip'),
                    'description' => 'Não foi possível enviar o SMS para: ' . $celular . ' - ' . $user->name . ', senha: ' . $senha . ': ' . $api_http,
                ]);
            } else {
                //LOG
                Log::create([
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
        $user = User::find($request->user_id);
        $senha = $this->gerar_senha(5, false, true, true, false);
        $user->password = bcrypt($senha);
        $user->save();
        //LOG
        Log::create([
            'user_id' => session('user_id'),
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Gerada nova senha de usuário (COMISSÃO): ' . $user->document . ' - ' . $user->name . ', senha: ' . $senha,
        ]);
        try {
            Mail::to($user->email)->send(new NovaSenhaUsuarioEmail($user, $senha));
        } catch (\Exception $e) {
            Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO',
                'ip' => session('ip'),
                'description' => 'Erro ao enviar e-mail: ' . $e->getMessage(),
            ]);
        }
        // Envio do SMS
        $celular = $user->mobile;
        $celular = trim($celular);
        $celular = str_replace(['+55'], [''], $celular);
        $celular = str_replace(['(', ')', '-'], ['', '', ''], $celular);
        if (strlen($celular) == 11) {
            $mesa = '';
            if ($user->poll) {
                $mesa = $user->poll->code;
            }
            $mensagem = urlencode("VOTAÇÕES SINDIFISCO-RS - endereço " . asset('/') . "op/" . $mesa . " sua nova senha de acesso e: " . $senha);
            // concatena a url da api com a variável carregando o conteúdo da mensagem
            $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $celular . "&mensagem={$mensagem}";
            // realiza a requisição http passando os parâmetros informados
            $api_http = file_get_contents($url_api);
            // imprime o resultado da requisição
            if ($api_http != 'OK') {
                //LOG
                Log::create([
                    'user_id' => session('user_id'),
                    'code' => 'SMS COM',
                    'ip' => session('ip'),
                    'description' => 'Não foi possível enviar o SMS para: ' . $celular . ' - ' . $user->name . ', senha: ' . $senha . ': ' . $api_http,
                ]);
            } else {
                //LOG
                Log::create([
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
        $user = User::find($request->user_id);
        $user->enabled_until = date('Y-m-d H:i:s', strtotime('+6 minutes',strtotime(date('Y-m-d H:i:s'))));
        $user->save();
        //LOG
        Log::create([
            'user_id' => session('user_id'),
            'code' => 'LIBERAR',
            'ip' => session('ip'),
            'description' => 'Usuário liberado 5 minutos (COMISSÃO): ' . $user->document . ' - ' . $user->name,
        ]);
        try {
            Mail::to($user->email)->send(new UsuarioLiberadoEmail($user));
        } catch (\Exception $e) {
            Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO',
                'ip' => session('ip'),
                'description' => 'Erro ao enviar e-mail: ' . $e->getMessage(),
            ]);
        }
        // Envio do SMS
        $celular = $user->mobile;
        $celular = trim($celular);
        $celular = str_replace(['+55'], [''], $celular);
        $celular = str_replace(['(', ')', '-'], ['', '', ''], $celular);
        if (strlen($celular) == 11) {
            $mesa = '';
            if ($user->poll) {
                $mesa = $user->poll->code;
            }
            $mensagem = urlencode("VOTAÇÕES SINDIFISCO-RS - endereço " . asset('/') . "op/" . $mesa . " usuario liberado (5 minutos)");
            // concatena a url da api com a variável carregando o conteúdo da mensagem
            $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $celular . "&mensagem={$mensagem}";
            // realiza a requisição http passando os parâmetros informados
            $api_http = file_get_contents($url_api);
            // imprime o resultado da requisição
            if ($api_http != 'OK') {
                //LOG
                Log::create([
                    'user_id' => session('user_id'),
                    'code' => 'SMS 5MIN',
                    'ip' => session('ip'),
                    'description' => 'Não foi possível enviar o SMS para: ' . $celular . ' - ' . $user->name . ': ' . $api_http,
                ]);
            } else {
                //LOG
                Log::create([
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
        Log::create([
            'user_id' => session('user_id'),
            'code' => $request->opcao,
            'ip' => session('ip'),
            'description' => $request->opcao . ' usuário: ' . $request->user_id,
        ]);
        try {
            $user = User::where('id', $request->user_id);
            $user->update(array('able' => $request->opcao === 'ativar' ? true : false));
            flash($request->opcao . ' usuário executado com sucesso: ' . $user->first()->name . '!')->success();
        } catch(\Exception $e) {
            //LOG
            Log::create([
                'user_id' => session('user_id'),
                'code' => $request->opcao,
                'ip' => session('ip'),
                'description' => 'Erro ao ' . $request->opcao . ' usuário: ' . $request->user_id . ': ' . $e->getMessage(),
            ]);
            flash('Erro ao ' . $request->opcao . ' usuário: ' . $user->first()->name . ' - ' . $e->getCode() . '!')->error();
        }
        return redirect(route('administrador'));
    }

    public function NovaSenha(Request $request)
    {
        $user = User::where('document', $request->cpf)->first();
        if (!$user) {
            flash('CPF Inválido!')->error();
            return redirect(route('/'));
        }
        $senha = $this->gerar_senha(5, false, true, true, false);
        $user->password = bcrypt($senha);
        $user->save();
        //LOG
        Log::create([
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Gerada nova senha de usuário (RESET): ' . $user->document . ' - ' . $user->name . ', senha: ' . $senha,
        ]);
        try {
            Mail::to($user->email)->send(new NovaSenhaUsuarioEmail($user, $senha));
        } catch (\Exception $e) {
            Log::create([
                'user_id' => session('user_id'),
                'code' => 'ERRO',
                'ip' => session('ip'),
                'description' => 'Erro ao enviar e-mail: ' . $e->getMessage(),
            ]);
        }
        // Envio do SMS
        $celular = $user->mobile;
        $celular = trim($celular);
        $celular = str_replace(['+55'], [''], $celular);
        $celular = str_replace(['(', ')', '-'], ['', '', ''], $celular);
        if (strlen($celular) == 11) {
            $mesa = '';
            if ($user->poll) {
                $mesa = $user->poll->code;
            }
            $mensagem = urlencode("VOTAÇÕES SINDIFISCO-RS - endereço " . asset('/') . "op/" . $mesa . " sua nova senha de acesso e: " . $senha);
            // concatena a url da api com a variável carregando o conteúdo da mensagem
            $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $celular . "&mensagem={$mensagem}";
            // realiza a requisição http passando os parâmetros informados
            $api_http = file_get_contents($url_api);
            // imprime o resultado da requisição
            if ($api_http != 'OK') {
                //LOG
                Log::create([
                    'code' => 'SMS RESET',
                    'ip' => session('ip'),
                    'description' => 'Não foi possível enviar o SMS para: ' . $celular . ' - ' . $user->name . ', senha: ' . $senha . ': ' . $api_http,
                ]);
            } else {
                //LOG
                Log::create([
                    'code' => 'SMS RESET',
                    'ip' => session('ip'),
                    'description' => 'SMS enviado: ' . $celular . ' - ' . $user->name . ', senha: ' . $senha . ': ' . $api_http,
                ]);
            }
        }
        flash('Nova senha gerada e enviada ao usuário ' . $user->name . '!')->success();
        return redirect(route('/'));
    }

    public function reminderAlteracaoEstatuto() {
        //OBTER NÃO VOTANTES DA POLL 28
        $usuarios = DB::select("SELECT U.id, U.name, U.email, U.mobile, uv.created_at FROM users U LEFT JOIN user_votes uv on U.id = uv.user_id WHERE U.poll_id = 28 AND U.id = 802 ORDER BY U.name;");
//        $usuarios = DB::select("SELECT U.id, U.name, U.email, U.mobile, uv.created_at FROM users U LEFT JOIN user_votes uv on U.id = uv.user_id WHERE U.poll_id = 28 AND U.id <> 802 ORDER BY U.name;");
        echo '<br><br>ENVIANDO REMINDERS PARA ' . count($usuarios) . ' ASSOCIADO(S)<br><br>';
        ob_flush();
        flush();
        $Log = '';
        //LOG
        Log::create([
            'user_id' => 1,
            'code' => 'UPLOAD',
            'ip' => session('ip'),
            'description' => 'Iniciando processo de reminder de usuários para ' . count($usuarios) . ' associados',
        ]);
        $count = 0;
        foreach ($usuarios as $usuario) {
            $count++;
            echo "{$count} - {$usuario->id}) {$usuario->name} [{$usuario->email}] / [{$usuario->mobile}]<br>";
            Log::create([
                'user_id' => $usuario->id,
                'code' => 'REMINDER',
                'ip' => session('ip'),
                'description' => 'Iniciando reminder do usuário ' . $usuario->name,
            ]);

            //EMAIL
            $email = trim($usuario->email);
            if ($email != '') {
                echo "TENTANDO ENVIAR E-MAIL PARA {$email}<br>";
                Log::create([
                    'user_id' => $usuario->id,
                    'code' => 'REMINDER',
                    'ip' => session('ip'),
                    'description' => 'Iniciando envio de e-mail para o endereço ' . $email,
                ]);
                Mail::to($email)->send(new ReminderUsuarioEmail($usuario->name));
                echo "E-MAIL ENVIADO<br>";
                Log::create([
                    'user_id' => $usuario->id,
                    'code' => 'REMINDER',
                    'ip' => session('ip'),
                    'description' => 'E-mail enviado com sucesso para o endereço ' . $email,
                ]);
            }

            //SMS
            $celular = $usuario->mobile;
            $celular = trim($celular);
            $celular = str_replace(['+55'], [''], $celular);
            $celular = str_replace(['(', ')', '-'], ['', '', ''], $celular);
            if (strlen($celular) == 11) {
                echo "TENTANDO ENVIAR SMS PARA {$celular}<br>";
                Log::create([
                    'user_id' => $usuario->id,
                    'code' => 'REMINDER',
                    'ip' => session('ip'),
                    'description' => 'Iniciando envio de SMS para ' . $celular,
                ]);
                $mensagem = urlencode("Associado,
Vote na alteração do Estatuto da AFISVEC.
Acesse o sistema de votação pelo link https://bityli.com/MY2zs com seu CPF e a senha enviada anteriormente por email e celular ou utilize o \"Solicitar Nova Senha\".
A alteração é muito importante para o associado e para a AFISVEC.");
                // concatena a url da api com a variável carregando o conteúdo da mensagem
                $url_api = "https://www.iagentesms.com.br/webservices/http.php?metodo=envio&usuario=Afisvec&senha=Rapunzel5&celular=" . $celular . "&mensagem={$mensagem}";
                // realiza a requisição http passando os parâmetros informados
                $api_http = file_get_contents($url_api);
                // imprime o resultado da requisição
                if ($api_http == 'OK') {
                    echo "SMS ENVIADO<br>";
                    Log::create([
                        'user_id' => $usuario->id,
                        'code' => 'REMINDER',
                        'ip' => session('ip'),
                        'description' => 'SMS enviado com sucesso para ' . $celular,
                    ]);
                } else {
                    echo "SMS COM ERRO<br>";
                    Log::create([
                        'user_id' => $usuario->id,
                        'code' => 'REMINDER',
                        'ip' => session('ip'),
                        'description' => 'Erro ao enviar SMS para ' . $celular . ': ' . $api_http,
                    ]);
                }
            }
        }
dd('FINALIZADO');
die();
    }

    public function UploadTempUser(Request $request)
    {
        //SALVAR O ARQUIVO
        $arquivo = $request->file('csvtemporario')->store('usuarios', 'public');

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
        //LOG
        Log::create([
            'user_id' => session('user_id'),
            'code' => 'UPLOAD_TEMP_USER',
            'ip' => session('ip'),
            'description' => 'Iniciando processo de upload de usuários temporários',
        ]);
        while(!feof($fileAberto)) {
            $linha = trim(fgets($fileAberto));
            if ($qtdeLinhasArquivo <= 0) {
                $qtdeLinhasArquivo = 1;
            } else
            {
                //LOG
                Log::create([
                    'user_id' => session('user_id'),
                    'code' => 'UPLOAD_TEMP_USER',
                    'ip' => session('ip'),
                    'description' => 'Processando linha: ' . $linha,
                ]);
                if ($linha != '') {
                    $intContadorInterno = $intContadorInterno + 1;
                    $qtdeLinhasArquivo = $qtdeLinhasArquivo + 1;
                    $qtdeLinhas = $qtdeLinhas + 1;

                    $linhaarray = explode(';', $linha);

                    if (count($linhaarray) != 17 ) {
                        $Log = $Log . 'Erro ao processar registro. Linha: ' . $linha . '<br>';
                        //LOG
                        Log::create([
                            'user_id' => session('user_id'),
                            'code' => 'ERRO',
                            'ip' => session('ip'),
                            'description' => 'Erro ao processar registro. Linha inválida: ' . $linha . '. Esperadas 17 colunas, encontrado: ' . count($linhaarray),
                        ]);
                        break;
                    }
                    $name = $linhaarray[0];
                    $document = str_replace(['.', '-'], ['', ''], $linhaarray[1]);
                    $code_area = $linhaarray[2];
                    $phone = $linhaarray[3];
//                    $phone2 = $linhaarray[4];
//                    $phone2_desc = $linhaarray[5];
//                    $phone3 = $linhaarray[6];
                    $address_type = $linhaarray[4];
                    $address = $linhaarray[5];
                    $address_number = $linhaarray[6];
                    $address_line2 = $linhaarray[7];
                    $pobox = $linhaarray[8];
                    $district = $linhaarray[9];
                    $zipcode = $linhaarray[10];
                    $city = $linhaarray[11];
                    $province = $linhaarray[12];
                    $email = $linhaarray[13];
                    $email2 = $linhaarray[14];
                    $birthday = $linhaarray[15];
                    if (strlen($birthday) == 10) {
                        $birthday = substr($birthday, 6, 4) . '-' . substr($birthday, 3, 2) . '-' . substr($birthday, 0, 2);
                    }
//                    $gender = $linhaarray[19];
                    $situation = $linhaarray[16];
                    $password_plain = $this->gerar_senha(5, false, true, true, false);
                    $password_bcrypt = bcrypt($password_plain);
                    $tempUser = TempUser::create([
                        'document' => $document,
                        'name' => $name,
                        'birthday' => $birthday,
                        'code_area' => $code_area,
                        'phone' => $phone,
                        'phone2' => '',
                        'phone2_desc' => '',
                        'phone3' => '',
                        'address_type' => $address_type,
                        'address' => $address,
                        'address_number' => $address_number,
                        'address_line2' => $address_line2,
                        'pobox' => $pobox,
                        'district' => $district,
                        'zipcode' => $zipcode,
                        'city' => $city,
                        'province' => $province,
                        'email' => $email,
                        'email2' => $email2,
                        'gender' => '',
                        'situation' => $situation,
                        'password_plain' => $password_plain,
                        'password_bcrypt' => $password_bcrypt,
                    ]);
                }
            }
        }
        DB::connection('mysql')->commit();
        //LOG
        Log::create([
            'user_id' => session('user_id'),
            'code' => 'UPLOAD_TEMP_USER',
            'ip' => session('ip'),
            'description' => 'Finalizando processo de upload de usuários temporários',
        ]);
        return redirect(route('administrador'));
    }

}
