<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistema de Votações - Associação dos Auditores Fiscais da Receita Estadual - RS">
    <meta name="author" content="Sócrates Duarte - socrates@swge.com.br">
    <title>Votação | AFISVEC - Área da Comissão</title>

    <!-- Bootstrap core CSS -->
    <link href="{{ asset("css/app.css") }}" rel="stylesheet">
    <link href="{{ asset("css/bootstrap.css") }}" rel="stylesheet">
{{--    <meta name="theme-color" content="#563d7c">--}}
</head>
<body class="text-center" style="background-color: #D3DEE2">

<div class="container-lg">
    <h1>&nbsp;</h1>
    <img class="mb-4" src="{{ asset("img/AFISVEC.png") }}" alt="">
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
                <div class="col-6">Nome</div>
                <div class="col-3 text-right">Ações</div>
            </div>
        </div>
        @foreach($usuarios as $usuario)
        <div class="list-group-item list-group-item-action text-left">
            <div class="row">
                <div class="col-1">{{ $usuario->id }}</div>
                <div class="col-2">{{ $usuario->document }}</div>
                <div class="col-6">{{ $usuario->name }}</div>
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
                <div class="col-4">Nome</div>
                <div class="col-4">Realização</div>
                <div class="col-3">Cadastrada em</div>
            </div>
        </div>
        <div class="list-group-item list-group-item-action text-left">
            <div class="row">
                <div class="col-1">{{ $poll->id }}</div>
                <div class="col-4">{{ $poll->name }}</div>
                <div class="col-4">{{ $poll->start->format('d/m/Y') . ' a ' . $poll->end->format('d/m/Y') }}</div>
                <div class="col-3">{{ $poll->created_at->format('d/m/Y H:i:s') }}</div>
            </div>
        </div>
        <?php
        $pollquestions = \App\PollQuestion::where('poll_id', $poll->id)->orderby('id', 'ASC')->get();
        if (count($pollquestions) > 0) {
            foreach ($pollquestions as $pollquestion) {
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
                $pollquestionoptions = \App\PollQuestionOption::where('poll_question_id', $pollquestion->id)->orderby('id', 'ASC')->get();
                if (count($pollquestionoptions) > 0) {
                    foreach ($pollquestionoptions as $pollquestionoption) {
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
