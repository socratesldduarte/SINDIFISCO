<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistema de Votações - Associação dos Auditores Fiscais da Receita Estadual - RS">
    <meta name="author" content="Sócrates Duarte - socrates@swge.com.br">
    <title>Votação | SINDIFISCO-RS - Área da Comissão</title>

    <!-- Bootstrap core CSS -->
    <link href="{{ asset("css/app.css") }}" rel="stylesheet">
    <link href="{{ asset("css/bootstrap.css") }}" rel="stylesheet">
{{--    <meta name="theme-color" content="#563d7c">--}}
</head>
<body class="text-center" style="background-color: #D3DEE2">

<div class="container-lg">
    <h1>&nbsp;</h1>
    <img class="mb-4" src="{{ asset("img/sindifisco.png") }}" alt="">
    <h1 class="h3 mb-3 font-weight-normal">Usuários Cadastrados</h1>
    <form name="frm_Acoes" id="frm_Acoes" method="post">
        @csrf
        <input type="hidden" name="user_id" id="user_id">
        <input type="hidden" name="opcao" id="opcao">
    </form>
    <div id="flashMessage">
        @include('flash::message')
    </div>
    <div class="list-group">
        <div class="list-group-item list-group-item-action text-left active">
            <div class="row">
                <div class="col-1">#</div>
                <div class="col-2">CPF</div>
                <div class="col-4">Eleição</div>
                <div class="col-5">Nome</div>
                <div class="col-4">E-mail</div>
                <div class="col-4">Celular</div>
                <div class="col-3 text-right">Ações</div>
            </div>
        </div>
        @foreach($usuarios as $usuario)
        <div class="list-group-item list-group-item-action text-left">
            <div class="row">
                <div class="col-1">{{ $usuario->id }}</div>
                <div class="col-2">{{ $usuario->document }}</div>
                <div class="col-4">@if($usuario->poll){{$usuario->poll->name}}@endif</div>
                <div class="col-5">{{ $usuario->name }}</div>
                <div class="col-4">{{ $usuario->email }}</div>
                <div class="col-4">{{ $usuario->mobile }}</div>
                <div class="col-3 text-right"><button class="btn-sm btn-primary" onclick="f_Email('{{ $usuario->id }}');">E-MAIL / SMS</button>&nbsp;<button class="btn-sm btn-warning" onclick="f_Liberar('{{ $usuario->id }}');">LIBERAR (5 MIN)</button></div>
            </div>
        </div>
        @endforeach
        <div class="list-group-item list-group-item-action text-right">
            <div class="row">
                {{ $usuarios->links() }}
            </div>
        </div>
    </div>
    <h1 class="h3 mb-3 font-weight-normal">Eleição Ativa</h1>
    <div class="list-group">
        @if($poll)
        <div class="list-group-item list-group-item-action text-left active">
            <div class="row">
                <div class="col-1">#</div>
                <div class="col-3">Tipo</div>
                <div class="col-4">Nome</div>
                <div class="col-2">Realização</div>
                <div class="col-2">Cadastrada em</div>
            </div>
        </div>
        <div class="list-group-item list-group-item-action text-left">
            <div class="row">
                <div class="col-1">{{ $poll->id }}</div>
                <div class="col-3">{{ $poll->polltype->name }}</div>
                <div class="col-4">{{ $poll->name }}</div>
                <div class="col-2">{{ $poll->start->format('d/m/Y H:i') . ' a ' . $poll->end->format('d/m/Y H:i') }}</div>
                <div class="col-2">{{ $poll->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>
        <?php
        if (count($poll->pollquestions) > 0) {
            foreach ($poll->pollquestions as $pollquestion) {
        ?>
            <div class="list-group-item list-group-item-action text-left">
                <div class="row">
                    <div class="col-1">&nbsp;</div>
                    <div class="col-3">{{ $pollquestion->question }}</div>
                    <div class="col-5">{{ $pollquestion->description }}</div>
                    <div class="col-3">Pode selecionar: {{ $pollquestion->selection_number }}</div>
                </div>
            </div>
        <?php
                if (count($pollquestion->pollquestionoptions) > 0) {
                    foreach ($pollquestion->pollquestionoptions as $pollquestionoption) {
        ?>
                <div class="list-group-item list-group-item-action text-left">
                    <div class="row">
                        <div class="col-2">&nbsp;</div>
                        <div class="col-1">{{ $pollquestionoption->order }}</div>
                        <div class="col-1">{{ $pollquestionoption->option }}</div>
                        <div class="col-8">{!! $pollquestionoption->description !!}</div>
                    </div>
                </div>
        <?php
                    }
                }
            }
        }
        ?>
        @endif
    </div>
    <h1 class="h3 mb-3 font-weight-normal">Log de Liberação de Usuários</h1>
    <div class="list-group">
        @if($liberacoes)
            <div class="list-group-item list-group-item-action text-left active">
                <div class="row">
                    <div class="col-1">#</div>
                    <div class="col-3">Comissão</div>
                    <div class="col-5">Atividade</div>
                    <div class="col-3">Data / IP</div>
                </div>
            </div>
            <?php
            foreach ($liberacoes as $liberacao) {
            ?>
            <div class="list-group-item list-group-item-action text-left">
                <div class="row">
                    <div class="col-1">{{ $liberacao->id }}</div>
                    <div class="col-3">{{ $liberacao->user->name }}</div>
                    <div class="col-5">{{ $liberacao->description }}</div>
                    <div class="col-3">{{ $liberacao->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
            </div>
            <?php
            }
            ?>
        @endif
    </div>
    <div class="list-group-item list-group-item-action text-right">
        <form action="/inicio-com" method="get" class="form-group">
            <div class="row">
                <div class="col-9">
                </div>
                <div class="col-3">
                    <button class="btn btn-group btn-danger">LOGOFF</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset("js/app.js") }}"></script>
<script src="{{ asset("js/jquery.js") }}"></script>
<script src="{{ asset("js/bootstrap.bundle.js") }}"></script>
<script src="{{ asset("js/bootstrap-session-timeout.js") }}"></script>
<script>
    $.sessionTimeout({
        keepAliveUrl: '{{ route('keep-alive') }}',
        logoutUrl: '{{ route('/') }}',
        redirUrl: '{{ route('com-locked') }}',
        warnAfter: 540000,
        redirAfter: 600000
    });

    function f_Email(usuario) {
        //ENVIAR REQUISIÇÃO
        $('input[name="user_id"]').val(usuario);
        $('form[name="frm_Acoes"]').attr('action', '{{ route('enviaremailcom') }}');
        $('form[name="frm_Acoes"]').submit();
    }

    function f_Liberar(usuario) {
        //ENVIAR REQUISIÇÃO
        $('input[name="user_id"]').val(usuario);
        $('form[name="frm_Acoes"]').attr('action', '{{ route('liberar5min') }}');
        $('form[name="frm_Acoes"]').submit();
    }
</script>
</body>
</html>
