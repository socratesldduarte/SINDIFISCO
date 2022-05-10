<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistema de Votações - Associação dos Auditores Fiscais da Receita Estadual - RS">
    <meta name="author" content="Sócrates Duarte - socrates@swge.com.br">
    <title>Votação | SINDIFISCO-RS - Área Administrativa</title>

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
        <form name="frm_Search" id="frm_Search" method="get" action="{{route('administrador')}}">
            <div class="list-group-item list-group-item-action text-left active">
                <div class="row">
                    <div class="col-9">
                        <input type="text" name="key" value="{{$key}}">
                    </div>
                    <div class="col-3 text-right">
                        <button type="submit" class="btn btn-group btn-warning">PESQUISAR</button>
                    </div>
                </div>
            </div>
        </form>
        <div class="list-group-item list-group-item-action text-left active">
            <div class="row">
                <div class="col-1">#</div>
                <div class="col-2">CPF</div>
                <div class="col-2">Eleição</div>
                <div class="col-4">Nome</div>
                <div class="col-3 text-right">Ações</div>
            </div>
        </div>
        @foreach($usuarios as $usuario)
        <div class="list-group-item list-group-item-action text-left">
            <div class="row">
                <div class="col-1">{{ $usuario->id }}</div>
                <div class="col-2">{{ $usuario->document }}</div>
                <div class="col-2">@if($usuario->poll){{$usuario->poll->name}}@endif</div>
                <div class="col-4">{{ $usuario->name }}</div>
                <div class="col-3 text-right">
                    <button class="btn-sm @if($usuario->administrator) btn-primary @else btn-secondary @endif" onclick="f_Administrador('{{ $usuario->id }}', '@if($usuario->administrator) 0 @else 1 @endif');">A</button>
                    <button class="btn-sm @if( $usuario->committee ) btn-primary @else btn-secondary @endif" onclick="f_Comissao('{{ $usuario->id }}', '@if($usuario->committee) 0 @else 1 @endif');">C</button>
                    @if($usuario->poll)
                        @if($usuario->poll->poll_type_id === 2)
                            <button class="btn-sm @if($usuario->can_be_voted) btn-success @else btn-danger @endif">V</button>
                        @endif
                    @endif
                    <button class="btn-sm btn-warning" onclick="f_Email('{{ $usuario->id }}');">NOTIF</button>
                    <button class="btn-sm @if($usuario->able) btn-success @else btn-danger @endif" @if($usuario->able) onclick="f_Alterar('inativar', '{{ $usuario->id }}');" @else onclick="f_Alterar('ativar', '{{ $usuario->id }}');" @endif>@if($usuario->able) AT @else IN @endif</button>
                </div>
            </div>
        </div>
        @endforeach
        <div class="list-group-item list-group-item-action text-right">
            <div class="row">
                {{ $usuarios->links() }}
            </div>
            <form action="{{ route('uploadusuariostemporarios') }}" method="post" enctype="multipart/form-data" class="form-group" onsubmit="return f_ConfirmaUsuarioTemporario();">
                @csrf
                <div class="row">
                    <div class="col-9">
                        <input type="file" name="csvtemporario" id="csvtemporario" class="form-control">
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-group btn-warning">IMPORTAR USUÁRIOS (TEMP)</button>
                    </div>
                </div>
            </form>
            <form action="{{ route('uploadusuarios') }}" method="post" enctype="multipart/form-data" class="form-group" onsubmit="return f_ConfirmaUsuario();">
                @csrf
                <div class="row">
                    <div class="col-9">
                        <input type="file" name="csv" id="csv" class="form-control">
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-group btn-warning">CRIAR USUÁRIOS</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <h1 class="h3 mb-3 font-weight-normal">Eleições Ativas</h1>
    <div class="list-group">
        @foreach($polls as $poll)
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
        @endforeach
        <div class="list-group-item list-group-item-action text-right">
            <form action="{{ route('uploadeleicao') }}" method="post" enctype="multipart/form-data" class="form-group" onsubmit="return f_ConfirmaEleicao();">
                @csrf
                <div class="row">
                    <div class="col-9">
                        <input type="file" name="csveleicao" id="csveleicao" class="form-control">
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-group btn-warning">CRIAR ELEIÇÃO</button>
                    </div>
                </div>
            </form>
            <form action="{{ route('inicio-adm') }}" method="get" class="form-group">
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
        redirUrl: '{{ route('adm-locked') }}',
        warnAfter: 540000,
        redirAfter: 600000
    });

    function f_ConfirmaUsuario() {
        if (document.getElementById('csv').files.length == 0) {
            alert('Não foi selecionado arquivo de usuários.\nPor favor verifique.');
            return false;
        }
        if (confirm('Confirma a importação de usuários?')) {
            return true;
        }
        return false;
    }

    function f_ConfirmaUsuarioTemporario() {
        if (document.getElementById('csvtemporario').files.length == 0) {
            alert('Não foi selecionado arquivo de usuários.\nPor favor verifique.');
            return false;
        }
        if (confirm('Confirma a importação de usuários?')) {
            return true;
        }
        return false;
    }

    function f_ConfirmaEleicao() {
        if (document.getElementById('csveleicao').files.length == 0) {
            alert('Não foi selecionado arquivo de eleição.\nPor favor verifique.');
            return false;
        }
        if (confirm('Ao realizar essa ação, será criada uma nova eleição. Confirma?')) {
            return true;
        }
        return false;
    }

    function f_Administrador(usuario, opcao) {
        if ({{ session('user_id') }} == usuario) {
            alert('Não é possível alterar permissão de administrador do usuário logado');
            return false;
        }
        opcao = opcao.trim();
        //ENVIAR REQUISIÇÃO
        $('input[name="user_id"]').val(usuario);
        $('input[name="opcao"]').val(opcao);
        $('form[name="frm_Acoes"]').attr('action', '{{ route('tornaradministrador') }}');
        $('form[name="frm_Acoes"]').submit();
    }

    function f_Comissao(usuario, opcao) {
        opcao = opcao.trim();
        //ENVIAR REQUISIÇÃO
        $('input[name="user_id"]').val(usuario);
        $('input[name="opcao"]').val(opcao);
        $('form[name="frm_Acoes"]').attr('action', '{{ route('tornarcomissao') }}');
        $('form[name="frm_Acoes"]').submit();
    }

    function f_Email(usuario) {
        //ENVIAR REQUISIÇÃO
        $('input[name="user_id"]').val(usuario);
        $('form[name="frm_Acoes"]').attr('action', '{{ route('enviaremail') }}');
        $('form[name="frm_Acoes"]').submit();
    }

    function f_Alterar(acao, usuario) {
        //ENVIAR REQUISIÇÃO
        $('input[name="user_id"]').val(usuario);
        $('input[name="opcao"]').val(acao);
        $('form[name="frm_Acoes"]').attr('action', '{{ route('inativar') }}');

        $('form[name="frm_Acoes"]').submit();
    }
</script>
</body>
</html>
