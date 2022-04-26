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
    <meta name="theme-color" content="#563d7c">

    <!-- Custom styles for this template -->
    <link href="{{ asset("css/signin.css") }}" rel="stylesheet">

</head>
<body class="text-center">
<form class="form-signin" method="post" action="{{ route('login') }}">
    @csrf
    <h1>&nbsp;</h1>
    <img class="mb-4" src="{{ asset("img/sindifisco.png") }}" alt="">
    <h1 class="h3 mb-3 font-weight-normal">Usuário Já Votou!</h1>

    <div id="flashMessage">
        @include('flash::message')
    </div>
    <p class="mt-5 mb-3 text-muted">&copy; 2022- <a href="https://www.sindifisco-rs.org.br/" target="_blank">SINDIFISCO-RS</a></p>
</form>
<script src="{{ asset("js/jquery.js") }}"></script>
<script src="{{ asset("js/bootstrap.bundle.js") }}"></script>
<script>
    setTimeout(function(){
        window.location.href = '{{ route('/') }}';
    }, 5000);
</script>
</body>
</html>
