<?php
session(['votacao_user_id' => '']);
session(['poll_id' => '1']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistema de Votações - Associação dos Auditores Fiscais da Receita Estadual - RS">
    <meta name="author" content="Sócrates Duarte - socrates@swge.com.br">
    <title>Votação | AFISVEC</title>

    <!-- Bootstrap core CSS -->
    <link href="{{ asset("css/app.css") }}" rel="stylesheet">
    <link href="{{ asset("css/bootstrap.css") }}" rel="stylesheet">

    <!-- Favicons -->
{{--    <link rel="apple-touch-icon" href="https://getbootstrap.com/docs/4.4/assets/img/favicons/apple-touch-icon.png" sizes="180x180">--}}
{{--    <link rel="icon" href="https://getbootstrap.com/docs/4.4/assets/img/favicons/favicon-32x32.png" sizes="32x32" type="image/png">--}}
{{--    <link rel="icon" href="https://getbootstrap.com/docs/4.4/assets/img/favicons/favicon-16x16.png" sizes="16x16" type="image/png">--}}
{{--    <link rel="manifest" href="https://getbootstrap.com/docs/4.4/assets/img/favicons/manifest.json">--}}
{{--    <link rel="mask-icon" href="https://getbootstrap.com/docs/4.4/assets/img/favicons/safari-pinned-tab.svg" color="#563d7c">--}}
{{--    <link rel="icon" href="https://getbootstrap.com/docs/4.4/assets/img/favicons/favicon.ico">--}}
{{--    <meta name="msapplication-config" content="https://getbootstrap.com/docs/4.4/assets/img/favicons/browserconfig.xml">--}}
    <meta name="theme-color" content="#563d7c">


    <!-- Custom styles for this template -->
    <link href="{{ asset("css/signin.css") }}" rel="stylesheet">

</head>
<body class="text-center">
<form class="form-signin" method="post" action="{{ route('login-administrador') }}">
    @csrf
    <h1>&nbsp;</h1>
    <img class="mb-4" src="{{ asset("img/AFISVEC.png") }}" alt="">
    <h1 class="h3 mb-3 font-weight-normal">Faça o login para iniciar<br>Área Administrativa</h1>

    <div id="flashMessage">
       @include('flash::message')
    </div>

    <label for="cpf" class="sr-only">CPF</label>
    <input type="text" name="cpf" id="cpf" class="form-control" placeholder="CPF" required autofocus maxlength="11">

    <label for="senha" class="sr-only">Senha</label>
    <input type="password" name="senha" id="senha" class="form-control" placeholder="Senha" required maxlength="20">

    <button class="btn btn-lg btn-primary btn-block" type="submit">Fazer Login</button>
    <p class="mt-5 mb-3 text-muted">&copy; 2020- <a href="https://afisvec.org.br/" target="_blank">AFISVEC</a></p>
</form>
<script src="{{ asset("js/jquery.js") }}"></script>
<script src="{{ asset("js/bootstrap.bundle.js") }}"></script>
</body>
</html>
