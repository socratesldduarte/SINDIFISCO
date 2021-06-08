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
    <meta name="theme-color" content="#563d7c">


    <!-- Custom styles for this template -->
    <link href="{{ asset("css/signin.css") }}" rel="stylesheet">

</head>
<body class="text-center">
<form name="frm_Documentos" class="form-signin" method="post" action="{{ route('autenticidade') }}">
    @csrf
    <h1>&nbsp;</h1>
    <img class="mb-4" src="{{ asset("img/AFISVEC.png") }}" alt="">
    @if(count($polls))
        @foreach($polls as $poll)
        <h1 class="h3 mb-3 font-weight-normal">{{ $poll->name }}</h1>
        <h5 class="h5 mb-3 font-weight-normal">De {{ $poll->start->format('d/m/Y H:i:s') }} a {{ $poll->end->format('d/m/Y H:i:s') }}</h5>
        <h5 class="h5 mb-3 font-weight-normal"><a href="{{ route('zeresima', ['poll_id' => $poll->id]) }}" target="_blank">ZERÉSIMA</a></h5>
        <h5 class="h5 mb-3 font-weight-normal"><a href="{{ route('boletimapuracao', ['poll_id' => $poll->id]) }}" target="_blank">BOLETIM DE APURAÇÃO</a></h5>
        <h5>&nbsp;</h5>
        @endforeach
    @endif
    <h5>&nbsp;</h5>
    <h6 class="h6 mb-3 font-weight-normal">Para acessar um documento emitido anteriormente, informe o código de autenticidade abaixo</h6>

    <div id="flashMessage">
       @include('flash::message')
    </div>

    <label for="cpf" class="sr-only">Código</label>
    <input type="text" name="hash" id="hash" class="form-control" placeholder="hash" required autofocus maxlength="40">

    <button type="submit" class="btn btn-lg btn-primary btn-block">Autenticar</button>
    <p class="mt-5 mb-3 text-muted">&copy; 2021- <a href="https://afisvec.org.br/" target="_blank">AFISVEC</a></p>
</form>
</body>
</html>
