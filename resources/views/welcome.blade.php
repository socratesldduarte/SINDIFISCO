<?php
session(['votacao_user_id' => '']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistema de Votações - Associação dos Auditores Fiscais da Receita Estadual - RS">
    <meta name="author" content="Sócrates Duarte - socrates@swge.com.br">
    <title>Votação | SINDIFISCO-RS</title>

    <!-- Bootstrap core CSS -->
    <link href="{{ asset("css/app.css") }}" rel="stylesheet">
    <link href="{{ asset("css/bootstrap.css") }}" rel="stylesheet">

    <!-- Favicons -->
    <meta name="theme-color" content="#563d7c">


    <!-- Custom styles for this template -->
    <link href="{{ asset("css/signin.css") }}" rel="stylesheet">

</head>
<body class="text-center">
@if ($poll)
<form name="frm_Login" class="form-signin" method="post" action="{{ route('login') }}">
    @csrf
    <h1> </h1>
    <img class="mb-4" src="{{ asset("img/sindifisco.png") }}" alt="">

    <h1 class="h3 mb-3 font-weight-normal">
        {{$poll->code}}<br>
        Faça o login para iniciar
    </h1>

    <div id="flashMessage">
       @include('flash::message')
    </div>

    <label for="cpf" class="sr-only">CPF</label>
    <input type="text" name="cpf" id="cpf" class="form-control" placeholder="CPF" required autofocus maxlength="11">

    <label for="birthday" class="sr-only is-invalid">Data de Nascimento</label>
    <input type="text" name="birthday" id="birthday" class="form-control" placeholder="Nascimento (01/01/1900)" maxlength="20">
    @error('birthday')<div class="alert alert-danger">{{ $message }}</div>@enderror

    <label for="senha" class="sr-only">Senha</label>
    <input type="password" name="senha" id="senha" class="form-control" placeholder="Senha" maxlength="20">

    <button class="btn btn-lg btn-primary btn-block" onclick="f_Login();">Fazer Login</button>
{{--    <button class="btn btn-lg btn-secondary btn-block" onclick="f_NovaSenha();">Solicitar Nova Senha</button>--}}
{{--    <h5 class="mt-5 mb-3 text-muted"><a href="https://afisvec.org.br/portal/wp-content/uploads/2020/05/Instruções.mp4" target="_blank">Dúvidas? Clique<br>para ver o vídeo.</a></h5>--}}
    <p class="mt-5 mb-3 text-muted">&copy; 2022- <a href="https://www.sindifisco-rs.org.br/" target="_blank">SINDIFISCO-RS</a></p>
</form>
@else
    @if(count($polls))
    <form name="frm_SelecionaMesa" class="form-signin" method="post" action="{{ route('login') }}">
        @csrf
        <h1> </h1>
        <img class="mb-4" src="{{ asset("img/sindifisco.png") }}" alt="">

        <h1 class="h3 mb-3 font-weight-normal">
            Selecione sua mesa para fazer o login
        </h1>
        @foreach($polls as $mesa)
        <p>
            <a href="{{route('eleicao.codigo', ['codigo' => $mesa->code])}}">{{$mesa->code}} - {{$mesa->name}}</a>
        </p>
        @endforeach
        {{--    <h5 class="mt-5 mb-3 text-muted"><a href="https://afisvec.org.br/portal/wp-content/uploads/2020/05/Instruções.mp4" target="_blank">Dúvidas? Clique<br>para ver o vídeo.</a></h5>--}}
        <p class="mt-5 mb-3 text-muted">&copy; 2022- <a href="https://www.sindifisco-rs.org.br/" target="_blank">SINDIFISCO-RS</a></p>
    </form>
    @endif
@endif
<script src="{{ asset("js/jquery.js") }}"></script>
<script src="{{ asset("js/bootstrap.bundle.js") }}"></script>
<script>
    function f_Login() {
        $('form[name="frm_Login"]').submit();
    }

    function f_NovaSenha() {
        $('form[name="frm_Login"]').attr('action', '{{ route('novasenha') }}');
        $('form[name="frm_Login"]').submit();
    }
</script>
</body>
</html>
