<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollQuestion;
use App\Models\PollQuestionOption;
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use PDF;

class AdminController extends Controller
{
    Public Function Documentos() {
        //DETERMINAR ELEIÇÃO ATIVA / ÚLTIMA
        $polls = Poll::where('active', true)->orderby('id', 'ASC')->get();
        return view('documentos', compact('polls'));
    }

    Public Function Zeresima($poll_id) {
        //EXISTE A ELEIÇÃO?
        $poll = Poll::find($poll_id);
        if (!$poll) {
            flash('Eleição Inexistente!')->error();
            return redirect()->route('documentos');
        }
        //ELEIÇÃO JÁ FOI ABERTA?
        if (!$poll->start > now()) {
            flash('Eleição Ainda Não Foi Inciada!')->error();
            return redirect()->route('documentos');
        }
        //ELEIÇÃO JÁ POSSUI ZERÉSIMA?
        $zeresima = Document::where('poll_id', $poll_id)->where('type', 'ZERESIMA')->first();
        if (empty($zeresima)) {
            $hash = md5(uniqid(rand(), true));
            $created_at = date_create($poll->start->format('Y-m-d H:i:s'));
            $second = random_int ( 0 , 30);
            $created_at = date_add($created_at, date_interval_create_from_date_string($second . " secs"));
            $content = '<div align="center"><img src="{{ asset("img/sindifisco.png") }}"></div>' .
                '<h2 style="text-align: center">ZERÉSIMA<br>'.
                $poll->name . '</h2>';
            //QUESTOES
            $pollquestions = PollQuestion::where('poll_id', $poll_id)->orderby('id')->get();
            foreach ($pollquestions as $pollquestion) {
                $content = $content . '<h3 style="text-align: center">' . $pollquestion->description . '</h3>' .
                    '<strong>Opção</strong>                                                              <strong>Votos</strong><br>';
                //OPÇÕES
                $pollquestionoptions = PollQuestionOption::where('poll_question_id', $pollquestion->id)->orderby('id')->get();
                foreach ($pollquestionoptions as $pollquestionoption) {
                    $votoscandidato = 0;
                    $opcao = $pollquestionoption->option . ' ' . $pollquestionoption->description;
                    if (strlen($opcao) >= 65) { $opcao = substr($opcao, 0, 65); }
                    $tamanho = 65 - strlen($opcao);
                    //$totalvotos = DB::select('SELECT COUNT(*) AS QTDE FROM user_vote_details WHERE poll_question_option_id = ' . $pollquestionoption->id . ';');
                    $content = $content . $opcao . ' ' . str_repeat(".", $tamanho) . ' ' . $votoscandidato . '<br>';
                }
            }
            //EMITIR ZERÉSIMA COM DATA DE INÍCIO DA ELEIÇÃO MAIS ALGUNS SEGUNDOS RANDOM ATÉ 10s
            Document::create([
                'poll_id' => $poll_id,
                'type' => 'ZERESIMA',
                'hash' => $hash,
                'content' => $content,
                'created_at' => date_format($created_at, 'Y-m-d H:i:s'),
            ]);
        }
        //OBTER A ZERESIMA E ENVIAR PARA A VIEW
        $zeresima = Document::where('poll_id', $poll_id)->where('type', 'ZERESIMA')->first();
        $pdf = PDF::loadView('zeresima', compact('zeresima'));
        return $pdf->setPaper('a4')->stream();
//        return view('zeresima', compact('zeresima'));
    }

    Public Function BoletimApuracao($poll_id) {
        //EXISTE A ELEIÇÃO?
        $poll = Poll::find($poll_id);
        if (!$poll) {
            flash('Eleição Inexistente!')->error();
            return redirect()->route('documentos');
        }
        //ELEIÇÃO JÁ FOI ABERTA?
        if (!($poll->end < now())) {
            flash('Eleição Ainda Não Foi Encerrada!')->error();
            return redirect()->route('documentos');
        }
        $encerramento = date_create($poll->end->format('Y-m-d H:i:s'));
        $encerramento = date_add($encerramento, date_interval_create_from_date_string(15 . " mins"));
        //VERIFICANDO SE FOI ENCERRADA HÁ MENOS DE 15 MINUTOS
        if (!($encerramento < now())) {
            flash('Boletim liberado somente após 15min!')->error();
            return redirect()->route('documentos');
        }
        //ELEIÇÃO JÁ POSSUI BOLETIM?
        $boletimapuracao = Document::where('poll_id', $poll_id)->where('type', 'BOLETIMAPURACAO')->first();
        if (empty($boletimapuracao)) {
            $hash = md5(uniqid(rand(), true));
            $content = '<div align="center"><img src="{{ asset("img/sindifisco.png") }}"></div>' .
                '<h2 style="text-align: center">BOLETIM DE APURAÇÃO<br>'.
                $poll->name . '</h2>';
            //QUESTOES
            $pollquestions = PollQuestion::where('poll_id', $poll_id)->orderby('id')->get();
            foreach ($pollquestions as $pollquestion) {
                $content = $content . '<h3 style="text-align: center">' . $pollquestion->description . '</h3>' .
                    '<strong>Opção</strong>                                                              <strong>Votos</strong><br>';
                //OPÇÕES
                $pollquestionoptions = PollQuestionOption::where('poll_question_id', $pollquestion->id)->orderby('id')->get();
                foreach ($pollquestionoptions as $pollquestionoption) {
                    $votoscandidato = 0;
                    $opcao = $pollquestionoption->option . ' ' . $pollquestionoption->description;
                    $totalvotos = DB::select('SELECT COUNT(*) AS QTDE FROM user_vote_details WHERE poll_question_option_id = ' . $pollquestionoption->id . ';');
                    $votoscandidato = $totalvotos[0]->QTDE;
                    if (strlen($opcao) >= 65) { $opcao = substr($opcao, 0, 65); }
                    $tamanho = 65 - strlen($opcao);
                    $content = $content . $opcao . ' ' . str_repeat(".", $tamanho) . ' ' . $votoscandidato . '<br>';
                }
            }
            //EMITIR BOLETIM
            Document::create([
                'poll_id' => $poll_id,
                'type' => 'BOLETIMAPURACAO',
                'hash' => $hash,
                'content' => $content,
            ]);
        }
        //OBTER O BOLETIM E ENVIAR PARA A VIEW
        $boletimapuracao = Document::where('poll_id', $poll_id)->where('type', 'BOLETIMAPURACAO')->first();
        $pdf = PDF::loadView('boletimapuracao', compact('boletimapuracao'));
        return $pdf->setPaper('a4')->stream();
    }

    public function Autenticidade(Request $request) {
        //POSSUI DOCUMENTO?
        $documento = Document::where('hash', $request->hash)->first();
        if (empty($documento)) {
            flash('HASH incorreto - por favor verifique!')->error();
            return redirect()->route('documentos');
        }
        //REDIRECIONAR PARA A VIEW CORRETA
        if ($documento->type = 'ZERESIMA') {
            $zeresima = $documento;
            $pdf = PDF::loadView('zeresima', compact('zeresima'));
            return $pdf->setPaper('a4')->stream();
        } else if ($documento->type == 'BOLETIMAPURACAO') {
            $boletimapuracao = $documento;
            $pdf = PDF::loadView('boletimapuracao', compact('boletimapuracao'));
            return $pdf->setPaper('a4')->stream();
        } else {
            flash('Documento inválido: ' . $documento->type)->error();
            return redirect()->route('documentos');
        }
    }
}
