<?php
session(['committee' => '']);
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

    <!-- Custom styles for this template -->
    <link href="{{ asset("css/signin.css") }}" rel="stylesheet">

</head>
<body class="text-center">
<form class="form-signin" method="get" action="{{ route('/') }}">
    <h1>&nbsp;</h1>
    <img class="mb-4" src="{{ asset("img/AFISVEC.png") }}" alt="">
    <h1 class="h3 mb-3 font-weight-normal">Sua Sessão Expirou!</h1>

    <div id="flashMessage">
       @include('flash::message')
    </div>

    <button class="btn btn-lg btn-primary btn-block" type="submit">Retornar à Tela Inicial</button>
    <p class="mt-5 mb-3 text-muted">&copy; 2021- <a href="https://afisvec.org.br/" target="_blank">AFISVEC</a></p>
</form>
<script src="{{ asset("js/jquery.js") }}"></script>
<script src="{{ asset("js/bootstrap.bundle.js") }}"></script>
</body>
</html>
