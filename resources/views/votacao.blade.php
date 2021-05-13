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

    <link href="{{ asset("css/smart_wizard.css") }}" rel="stylesheet">
    <link href="{{ asset("css/smart_wizard_theme_arrows.css") }}" rel="stylesheet">

</head>
<body class="text-center" style="background-color: #D3DEE2">
<form class="form-votacao" method="post" action="{{ route('registrovoto') }}">
    <input type="hidden" name="votacao_user_id" value="{{ session('votacao_user_id') }}">
    @csrf
    <h1> </h1>
    <img class="mb-4" src="{{ asset("img/AFISVEC.png") }}" alt="">
    <h1 class="h3 mb-3 font-weight-normal">{{ $poll->name }}</h1>
    <h2 class="h3 mb-3 font-weight-normal">Eleitor: {{ $user->name }}</h2>
    <div class="container">
        <div style="background-color: #FFFFFF; padding: 20px 0px 20px 0px;">
            <div class="col-12">
                <div id="flashMessage">
                    @include('flash::message')
                </div>
                <div id="smartwizard">
                    <ul>
                        <?php $intCount = 0; ?>
                        @foreach($poll->pollquestions as $pollquestion)
                            <?php $intCount += 1; ?>
                            <li>
                                <a href="#step-{{ $intCount }}">
                                    {{ $pollquestion->question }}
                                    <br /><small>{{ $pollquestion->description }}</small>
                                </a>
                            </li>
                        @endforeach
                        <li>
                            <a href="#step-{{ $intCount + 1 }}">
                                Confirmação
                                <br /><small>Confirmação de envio do voto</small>
                            </a>
                        </li>
                    </ul>

                    <div>
                        <?php $intCount = 0; ?>
                        @foreach($poll->pollquestions as $pollquestion)
                            <?php $intCount += 1; ?>
                            <input type="hidden" name="selecao_{{$intCount}}" value="{{$pollquestion->selection_number}}">
                            <div id="step-{{ $intCount }}" class="">
                                <h4>&nbsp;</h4>
                                @if( $pollquestion->selection_number == 1 )
                                    <h4>Escolha sua opção única.</h4>Caso não seja selecionada opção, o voto será considerado BRANCO
                                @else
                                    <h4>Escolha até {{ $pollquestion->selection_number }} opções.</h4>Caso não seja selecionado algum item, o voto será considerado BRANCO
                                @endif
                                <div class="form-group">
                                    <?php
                                    foreach ($pollquestion->pollquestionoptions()->where('order', '<>', 2)->where('order', '<>', 999)->orderby('order')->get() as $pollquestionoption) {
                                        if ( $pollquestion->selection_number == 1 ) {
                                    ?>
                                        <br><input type="radio" class="form-group" name="questao_{{ $pollquestion->id }}" value="{{ $pollquestionoption->id }}">&nbsp;{{ $pollquestionoption->option . ' - ' }} {!! $pollquestionoption->description !!}<br>
                                    <?php
                                        } else {
                                    ?>
                                        <br><input type="checkbox" class="form-group" name="questao_{{ $pollquestion->id }}[]" id="questao_{{$intCount - 1}}" value="{{ $pollquestionoption->id }}">&nbsp;{{ $pollquestionoption->option . ' - ' }} {!! $pollquestionoption->description !!}<br>
                                    <?php
                                        }
                                    }
                                    $pollquestionoptionNulo = $pollquestion->pollquestionoptions()->where('poll_question_id', $pollquestion->id)->where('order', 999)->first();
                                    if ($pollquestionoptionNulo) {
                                        if ( $pollquestion->selection_number == 1 ) {
                                            if ($pollquestionoptionNulo) {
                                    ?>
                                        <br><input type="radio" class="form-group" name="questao_{{ $pollquestion->id }}" value="{{ $pollquestionoptionNulo->id }}">&nbsp;{{ $pollquestionoptionNulo->option . ' - ' }} {!! $pollquestionoptionNulo->description !!}<br>
                                    <?php
                                            }
                                        } else {
                                            if ($pollquestionoptionNulo) {
                                                for ($i = 1; $i <= $pollquestion->selection_number; $i++) {
                                    ?>
                                        <input type="checkbox" class="form-group" name="questao_{{ $pollquestion->id }}[]" id="questao_{{$intCount - 1}}" value="{{ $pollquestionoptionNulo->id }}">&nbsp;{{ $pollquestionoptionNulo->option . ' - ' }} {!! $pollquestionoptionNulo->description !!}&nbsp;{{ $i }}º Voto<br><br>
                                    <?php
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        @endforeach
                        <div id="step-{{ $intCount + 1 }}" class="">
                            <h4>&nbsp;</h4>
                            <h4>Confirmação de envio de voto</h4>
                            <strong>Ao clicar no botão de confimação, o voto será registrado no banco de dados e não poderá mais ser acessado ou alterado.</strong>
                            <h1>&nbsp</h1>
                            <div align="right">
                                <input type="submit" class="btn btn-md btn-primary" value="Confirmar" align="right" style="margin-right:10px;">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</form>

{{--<script src="{{ asset("js/app.js") }}"></script>--}}

<script src="{{ asset("js/jquery.js") }}"></script>
<script src="{{ asset("js/bootstrap.bundle.js") }}"></script>
<script src="{{ asset("js/jquery.smartWizard.js") }}"></script>

<script type="text/javascript">
    $('#smartwizard').smartWizard({
        backButtonSupport: false, // Enable the back button support
        lang: {  // Language variables
            next: 'Próximo',
        },
        toolbarSettings: {
            showPreviousButton: false, // show/hide a Previous button
        },
        anchorSettings: {
            anchorClickable: false, // Enable/Disable anchor navigation
            enableAllAnchors: false, // Activates all anchors clickable all times
            markDoneStep: true, // add done css
            enableAnchorOnDoneStep: true // Enable/Disable the done steps navigation
        },
        contentURL: null, // content url, Enables Ajax content loading. can set as data data-content-url on anchor
        disabledSteps: [],    // Array Steps disabled
        errorSteps: [],    // Highlight step with errors
        theme: 'arrows',
        transitionEffect: 'slide', // Effect on navigation, none/slide/fade
        transitionSpeed: '400',
    });

    // Initialize the leaveStep event
    $("#smartwizard").on("leaveStep", function(e, anchorObject, stepNumber, stepDirection) {
        if (stepNumber < {{ count($poll->pollquestions) }}) {
            //DETERMINAR QUANTOS ITENS FORAM SELECIONADOS
            if ($("input[name='selecao_" + (stepNumber + 1) + "']").val() > 1) {
                //É CHECKBOX
                $qtde = 0;
                $.each($("#questao_" + stepNumber + ":checked"), function(){
                    $qtde = $qtde + 1;
                });
                if ($qtde < $("input[name='selecao_" + (stepNumber + 1) + "']").val()) {
                    return confirm("Confima o voto selecionado nessa categoria?\n\nCOMO VOCÊ SELECIONOU MENOS OPÇÕES DO QUE O PERMITIDO, OS VOTOS ADICIONAIS SERÃO CONSIDERADOS BRANCOS!\n\nAPÓS CONFIRMAR O VOTO EM UMA CATEGORIA, NÃO SERÁ MAIS POSSÍVEL ALTERÁ-LO!!!");
                }
                else if ($qtde == $("input[name='selecao_" + (stepNumber + 1) + "']").val()) {
                    return confirm("Confima o voto selecionado nessa categoria?\n\nAPÓS CONFIRMAR O VOTO EM UMA CATEGORIA, NÃO SERÁ MAIS POSSÍVEL ALTERÁ-LO!!!");
                } else {
                    alert("ATENÇÃO!!! VOCÊ SELECIONOU MAIS ITENS DO QUE O PERMITIDO NESSA CATEGORIA.\n\nPOR FAVOR VERIFIQUE E CORRIJA!!!");
                    return false;
                }
            } else {
                return confirm("Confima o voto selecionado nessa categoria?\n\nSE NÃO FOR SELECIONADA NENHUMA OPÇÃO, O VOTO SERÁ CONSIDERADO BRANCO NESTA CATEGORIA!\n\nAPÓS CONFIRMAR O VOTO EM UMA CATEGORIA, NÃO SERÁ MAIS POSSÍVEL ALTERÁ-LO!!!");
            }
        }
    });

    $("#smartwizard").on("showStep", function(e, anchorObject, stepNumber, stepDirection) {
        if($('button.sw-btn-next').hasClass('disabled')){
            $('.button.sw-btn-next').removeClass("disabled");
            $('#teste').removeClass("d-none");
        }

    });
</script>

<script src="{{ asset("js/bootstrap-session-timeout.js") }}"></script>
<script>
    $.sessionTimeout({
        keepAliveUrl: '{{ route('keep-alive') }}',
        logoutUrl: '{{ $poll->code ? route('eleicao.codigo', ['codigo' => $poll->code]) : route('/') }}',
        redirUrl: '{{ route('votacao-locked') }}',
        warnAfter: 540000,
        redirAfter: 600000
    });
</script>
</body>
</html>
