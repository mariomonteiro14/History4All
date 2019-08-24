<?php

namespace App\Http\Controllers;

use App\Atividade;
use App\AtividadeParticipantes;
use App\AtividadePatrimonios;
use App\AtividadeTestemunhos;
use App\Chat;
use App\Http\Resources\Atividade as AtividadeResource;
use App\ChatMensagens;
use App\Http\Resources\ShortAtividade as ShortAtividadeResource;
use App\Http\Resources\User as UserResources;
use App\Http\Resources\ChatMensagens as ChatMensagensResource;
use App\Notificacao;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AtividadeControllerAPI extends Controller
{
    public function getAtividade(Request $request, $id)
    {
        $atividade = Atividade::findOrFail($id);

        if ($atividade->visibilidade != 'publico') {
            if ($atividade->visibilidade == 'privado') {
                if (!AtividadeParticipantes::where('atividade_id', $id)->where('user_id', Auth::id())->first()
                    && $atividade->coordenador != Auth::id()) {
                    return abort(403, "Não pode aceder a esta informaçao");
                }
            }

            if ($atividade->coordenador()->escola_id != Auth::user()->escola_id) {
                return abort(403, "Não pode aceder a esta informaçao");
            }
        }

        if (Auth::user()->tipo == 'aluno') {
            return response()->json([
                'data' => new AtividadeResource($atividade)
            ]);
        }

        return response()->json([
            'data' => new AtividadeResource($atividade)
        ]);
    }


    public function getPendentes(Request $request, $id)
    {
        return response()->json([
            'data' => ShortAtividadeResource::collection(Atividade::join('atividade_participantes', 'atividade_id', 'id')
                ->where('user_id', $id)->whereNull('dataFinal')->orWhere('dataFinal', '>', Carbon::now()->toDateString())->get())
        ]);
    }

    public function getConcluidas(Request $request, $id)
    {
        return response()->json([
            'data' => ShortAtividadeResource::collection(Atividade::join('atividade_participantes', 'atividade_id', 'id')
                ->where('user_id', $id)->where('dataFinal', '<=', Carbon::now()->toDateString())->get())
        ]);
    }

    public function getMinhas(Request $request)
    {
        $user = Auth::user();
        if ($user->tipo == "professor") {
            return response()->json([
                'data' => ShortAtividadeResource::collection(Atividade::where('coordenador', $user->id)->get())
            ]);
        }
        return response()->json([
            'data' => ShortAtividadeResource::collection(Atividade::join('atividade_participantes', 'atividade_id', 'id')
                ->where('user_id', $user->id)->get())
        ]);
    }

    public function atividadesPublicas()
    {
        if (Auth::user()->tipo != "admin") {
            return ShortAtividadeResource::collection(Atividade::select('atividades.*')
                ->join('users', 'users.id', 'coordenador')
                ->where(function ($q) {
                    $q->where('escola_id', Auth::user()->escola_id)
                        ->where('visibilidade', 'LIKE', 'visivel para a escola');
                })
                ->orWhere('visibilidade', 'LIKE', 'publico')
                ->distinct()->get());


        }
        return ShortAtividadeResource::collection(Atividade::where('visibilidade', 'LIKE', 'publico')
            ->distinct()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|min:3',
            'descricao' => 'required|min:10:max:1000',
            'tipo' => 'required',
            'numeroElementos' => 'required|numeric|digits_between:1,6',
            'visibilidade' => 'required|string',
            'dataInicio' => 'required',
            'dataFinal' => 'nullable',
        ]);
        if (Auth::user()->tipo !== "professor") {
            abort(403, 'Unauthorized action.');
        }

        $atividade = new Atividade();
        $atividade->fill([
            'titulo' => $request->get('titulo'),
            'descricao' => $request->get('descricao'),
            'tipo' => $request->get('tipo'),
            'numeroElementos' => $request->get('numeroElementos'),
            'visibilidade' => $request->get('visibilidade'),
            'coordenador' => Auth::id(),
            'dataInicio' => $request->dataInicio,
            'dataFinal' => $request->dataFinal ? $request->dataFinal : null,
        ]);
        if ($request->get('chatExist')) {
            $chat = new Chat();
            if ($request->chat['assunto'] && $request->chat['assunto'] != "") {
                $chat->assunto = $request->chat['assunto'];
            }
            $chat->save();
            $atividade->chat_id = $chat->id;
        }
        $atividade->save();

        if ($request->has('participantes') && sizeof($request->get('participantes')) > 0) {
            $coordenador = User::findOrFail(Auth::id());

            $notificacaoMensagem = "Foi inserido(a) na atividade " . $atividade->titulo . " que está a ser coordenada pelo(a) porfessor(a) " . $coordenador->nome;
            $assunto = "Foi inserido(a) na atividade" . $atividade->titulo;
            $emailMensagem = "<h3>Foi inserido(a) na atividade" . $atividade->titulo . "</h3><p>A atividade está a ser coordenada pelo(a) porfessor(a) " .
                $coordenador->nome . "</p><p>A atividade consiste em: " . $atividade->descricao . "</p>";
            $link = 'atividade/' . $atividade->id;

            foreach ($request->participantes as $participante) {
                $atividadeParticipante = new AtividadeParticipantes();
                $atividadeParticipante->fill([
                    'atividade_id' => $atividade->id,
                    'user_id' => $participante['id']
                ]);
                $atividadeParticipante->save();
                $this->notificacaoEEmail(User::findOrFail($participante['id']), $notificacaoMensagem, $assunto, $emailMensagem, $link);
            }
        }

        if ($request->has('patrimonios') && sizeof($request->patrimonios) > 0) {
            foreach ($request->patrimonios as $patrimonio) {
                $atividadePatrimonio = new AtividadePatrimonios();
                $atividadePatrimonio->fill([
                    'atividade_id' => $atividade->id,
                    'patrimonio_id' => $patrimonio['id'],
                ]);
                $atividadePatrimonio->save();
            }
        }
        return response()->json(new AtividadeResource($atividade), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|min:3',
            'descricao' => 'required|min:10:max:1000',
            'tipo' => 'required',
            'numeroElementos' => 'required|numeric|digits_between:1,6',
            'visibilidade' => 'required|string',
            'dataInicio' => 'required',
            'dataFinal' => 'nullable',
        ]);

        $atividade = Atividade::findOrFail($id);
        if (Auth::user()->tipo == "professor" && $atividade->coordenador != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        $coordenador = User::findOrFail(Auth::id());

        //chat
        if (!$atividade->chat_id && $request->get('chatExist')) {//cria
            $chat = new Chat();
            $chat->assunto = $request->chat['assunto'];
            $chat->save();
            $atividade->chat_id = $chat->id;
        } else if ($atividade->chat_id && $request->get('chatExist')) {//atualiza
            $chat = Chat::findOrFail($atividade->chat_id);
            $chat->assunto = $request->chat['assunto'];
            $chat->save();
        } else if ($atividade->chat_id && !$request->get('chatExist')) {//apaga
            $oldChatId = $atividade->chat_id;
            $atividade->chat_id = null;
            Chat::findOrFail($oldChatId)->delete();
        }
        //participantes
        if (sizeof($request->get('participantes')) > 0) {
            $participantesRecebidos = array_column($request->get('participantes'), 'id');
            $participantes = AtividadeParticipantes::where('atividade_id', $id)->get()->pluck('user_id')->toArray();
            $inseridos = array_diff($participantesRecebidos, $participantes);//array de id's

            $notificacaoMensagem = "Foi inserido(a) na atividade " . $atividade->titulo . " que está a ser coordenada pelo(a) porfessor(a) " . $coordenador->nome;
            $assunto = "Foi inserido(a) na atividade" . $atividade->titulo;
            $emailMensagem = "<h3>Foi inserido(a) na atividade" . $atividade->titulo . "</h3><p>A atividade está a ser coordenada pelo(a) porfessor(a) " .
                $coordenador->nome . "</p><p>A atividade consiste em: " . $atividade->descricao . "</p>";
            $link = 'atividade/' . $atividade->id;

            foreach ($inseridos as $participanteId) {
                $atividadeParticipante = new AtividadeParticipantes();
                $atividadeParticipante->fill([
                    'atividade_id' => $id,
                    'user_id' => $participanteId,
                ]);
                $atividadeParticipante->save();
                $this->notificacaoEEmail(User::findOrFail($participanteId), $notificacaoMensagem, $assunto, $emailMensagem, $link);
            }
            $removidos = array_diff($participantes, $participantesRecebidos);

            $notificacaoMensagem = "Foi removido(a) da atividade " . $atividade->titulo . " que é coordenada pelo(a) porfessor(a) " . $coordenador->nome;
            $assunto = "Foi removido(a) na atividade " . $atividade->titulo;
            $emailMensagem = "<h3>Foi removido(a) na atividade " . $atividade->titulo . "</h3><p>A atividade é coordenada pelo(a) porfessor(a) " .
                $coordenador->nome . "</p>";

            foreach ($removidos as $participanteId) {
                $participante = AtividadeParticipantes::where('atividade_id', $id)->where('user_id', $participanteId);
                $participante->delete();
                $this->notificacaoEEmail(User::findOrFail($participanteId), $notificacaoMensagem, $assunto, $emailMensagem, null);
            }
        }
        //patrimonios
        if ($request->has('patrimonios') && sizeof($request->get('patrimonios')) > 0) {
            $patrimoniosRecebidos = array_column($request->get('patrimonios'), 'id');
            $patrimonios = AtividadePatrimonios::where('atividade_id', $id)->get()->pluck('patrimonio_id')->toArray();
            $inseridos = array_diff($patrimoniosRecebidos, $patrimonios);
            foreach ($inseridos as $patrimonioId) {
                $patrimonio = new AtividadePatrimonios();
                $patrimonio->fill([
                    'atividade_id' => $id,
                    'patrimonio_id' => $patrimonioId,
                ]);
                $patrimonio->save();
            }
            $removidos = array_diff($patrimonios, $patrimoniosRecebidos);
            if (count($request->get('patrimonios')) != AtividadePatrimonios::where('atividade_id', $id)->count()) {
                foreach ($removidos as $patrimonioId) {
                    AtividadePatrimonios::where('atividade_id', $id)->where('patrimonio_id', $patrimonioId)->delete();
                }
            }
        }
        $alteracoes = [];
        if ($atividade->tipo != $request->tipo) {
            array_push($alteracoes, "o tipo de atividade passou de " . $atividade->tipo . " para " . $request->tipo);
        }
        if ($atividade->numeroElementos != $request->numeroElementos) {
            array_push($alteracoes, "o número de elementos por grupo passou de " . $atividade->numeroElementos . " para " . $request->numeroElementos);
        }
        if ($atividade->dataInicio != $request->dataInicio) {
            array_push($alteracoes, "a data inicial passou a ser " . date('d-m-Y', strtotime($request->dataInicio)));
        }
        if ($atividade->dataFinal != $request->dataFinal) {
            array_push($alteracoes, "a data limite passou a ser " . date('d-m-Y', strtotime($request->dataFinal)));
        }
        if (count($alteracoes) > 0) {
            $alteracoesFormatadas = "";
            $alteracoesFormatadasHtml = "<ul>";
            foreach ($alteracoes as $indice => $alt) {
                $alteracoesFormatadas .= $alt;
                $alteracoesFormatadasHtml .= "<li>" . $alt . "</li>";
                if ($indice != count($alteracoes) - 1){
                    $alteracoesFormatadas .= ", ";
                } else{
                    $alteracoesFormatadas .= ".";
                }
            }
            $alteracoesFormatadasHtml .= "</ul>";
            $ids = AtividadeParticipantes::where('atividade_id', $id)->get()->pluck('user_id')->toArray();

            $notificacaoMensagem = "As especificações da atividade " . $request->titulo . " foram alteradas, " . $alteracoesFormatadas;
            $assunto = "A atividade " . $request->titulo . " foi alterada";
            $emailMensagem = "<h3>As especificações da atividade " . $request->titulo . " foram alteradas</h3><p>A atividade 
                é coordenada pelo(a) porfessor(a) " . $coordenador->nome . " e passou a ter as seguintes configurações: " .
                $alteracoesFormatadasHtml . "</p>";
            $link = 'atividade/' . $atividade->id;

            foreach ($ids as $id) {
                $this->notificacaoEEmail(User::findOrFail($id), $notificacaoMensagem, $assunto, $emailMensagem, $link);
            }
        }


        $atividade->fill([
            'titulo' => $request->get('titulo'),
            'descricao' => $request->get('descricao'),
            'tipo' => $request->get('tipo'),
            'numeroElementos' => $request->get('numeroElementos'),
            'visibilidade' => $request->get('visibilidade'),
            'dataInicio' => $request->dataInicio,
            'dataFinal' => $request->dataFinal ? $request->dataFinal : null,
        ]);

        $atividade->save();
        return response()->json(new AtividadeResource($atividade), 201);
    }

    public function destroy($id)
    {
        $atividade = Atividade::findOrFail($id);
        $atividade->delete();
        return response()->json(null, 201);
    }


    public function storeChatMensagem(Request $request)
    {
        $request->validate([
            'chat_id' => 'required',
            'mensagem' => 'required|min:1',
            'user_id' => 'required',
        ]);
        User::findOrFail($request->user_id);
        Chat::findOrFail($request->chat_id);

        $mensagem = new ChatMensagens();
        $mensagem->fill($request->all());
        $mensagem->save();

        return response()->json(new ChatMensagensResource($mensagem), 201);
    }

    public function getTestemunhos(Request $request, $id)
    {

        $atividade = Atividade::findOrFail($id);

        $testemunhos = $atividade->testemunhos()->exists() ? $atividade->testemunhos()->select('user_id', 'texto', 'rate', 'confirmado')->get() : [];
        return response()->json($testemunhos, 201);

    }

    public function novoTestemunho(Request $request, $id)
    {
        $atividade = Atividade::findOrFail($id);

        $request->validate([
            'texto' => 'required|string',
            'rate' => 'required|numeric|min:1|max:5',
        ]);
        if (!AtividadeParticipantes::where("atividade_id", $id)->where("user_id", $request->user_id)->first()
            && $atividade->coordenador != Auth::id()) {
            return abort(403, "Nao pode escrever testemunho nesta atividade");
        }
        if (AtividadeTestemunhos::where('atividade_id', $id)->where('user_id', $request->user_id)->first()) {
            return abort(403, "Nao pode submeter mais do que uma vez");
        }

        if (!AtividadeParticipantes::where('atividade_id', $id)->where('user_id', Auth::id())->first()
            && $atividade->coordenador != Auth::id()) {
            return abort(403, "Nao pode submeter um testemunho");
        }

        $testemunho = new AtividadeTestemunhos();
        $testemunho->fill([
            'atividade_id' => $id,
            'user_id' => $request->user_id,
            'texto' => $request->texto,
            'rate' => $request->rate,
            'confirmado' => 0
        ]);
        if ($atividade->coordenador == Auth::id()) {
            $testemunho->confirmado = 1;
        } else {
            $notif = new Notificacao();
            $notif->fill([
                'user_id' => $atividade->coordenador,
                'mensagem' => "Um aluno submeteu um novo testemunho na atividade " . $atividade->titulo . ".",
                'remetente' => "Aluno",
                'data' => date("Y-m-d H:i:s"),
                'lida' => "0",
                'link' => "atividade/" . $atividade->id
            ]);
            $notif->save();
        }
        $testemunho->save();
        return response()->json($testemunho, 201);
    }

    public function editTestemunho(Request $request, $id)
    {

        $request->validate([
            'texto' => 'required|string',
            'rate' => 'required|numeric|min:1|max:5',
        ]);
        $atividade = Atividade::findOrFail($id);

        if (Auth::id() != $request->user_id) {
            abort(403, "Nao tem permissoes");
        }

        $testemunho = $testemunho = AtividadeTestemunhos::where('atividade_id', $id)->where('user_id', $request->user_id)->first();
        $testemunho->texto = $request->texto;
        $testemunho->rate = $request->rate;

        if ($atividade->coordenador == Auth::id()) {
            $testemunho = AtividadeTestemunhos::where('atividade_id', $id)->where('user_id', $request->user_id)
                ->update(['texto' => $request->texto, 'rate' => $request->rate, 'confirmado' => 1]);
        } else {
            $confirmado = AtividadeTestemunhos::where('atividade_id', $id)->where('user_id', $request->user_id)->pluck('confirmado');
            $testemunho = AtividadeTestemunhos::where('atividade_id', $id)->where('user_id', $request->user_id)
                ->update(['texto' => $request->texto, 'rate' => $request->rate, 'confirmado' => 0]);

            if($confirmado[0] == 1) {
                $notif = new Notificacao();
                $notif->fill([
                    'user_id' => $atividade->coordenador,
                    'mensagem' => "Um aluno alterou um testemunho na atividade " . $atividade->titulo . ".",
                    'remetente' => "Aluno",
                    'data' => date("Y-m-d H:i:s"),
                    'lida' => "0",
                    'link' => "atividade/" . $atividade->id
                ]);
                $notif->save();
            }
        }
        return response()->json($confirmado, 201);
    }

    public function confirmarTestemunho(Request $request, $id, $user_id)
    {
        $atividade = Atividade::findOrFail($id);
        if ($atividade->coordenador != Auth::id()) {
            abort(403, "Nao tem permissoes");
        }
        $testemunho = AtividadeTestemunhos::where('atividade_id', $id)->where('user_id', $user_id)
            ->update(['confirmado' => 1]);

        $notif = new Notificacao();
        $notif->fill([
            'user_id' => $user_id,
            'mensagem' => "O seu testemunho na atividade " . $atividade->titulo . " foi confirmado.",
            'remetente' => "Coordenador da atividade " . $atividade->titulo,
            'data' => date("Y-m-d H:i:s"),
            'lida' => "0",
            'link' => "atividade/" . $atividade->id
        ]);
        $notif->save();

        return response()->json($testemunho, 201);
    }

    public function removerTestemunho(Request $request, $id, $user_id)
    {
        $atividade = Atividade::findOrFail($id);
        if (Auth::id() != $user_id && Auth::id() != $atividade->coordenador) {
            abort(403, "Nao tem permissoes");
        }

        if (Auth::id() != $user_id) {
            $notif = new Notificacao();
            $notif->fill([
                'user_id' => $user_id,
                'mensagem' => "O seu testemunho na atividade " . $atividade->titulo . " foi recusado.",
                'remetente' => "Coordenador da atividade " . $atividade->titulo,
                'data' => date("Y-m-d H:i:s"),
                'lida' => "0",
                'link' => "atividade/" . $atividade->id
            ]);
            $notif->save();
        }

        AtividadeTestemunhos::where('atividade_id', $id)->where('user_id', $user_id)->delete();

        return response()->json(null, 201);
    }
}
