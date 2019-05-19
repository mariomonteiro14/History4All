<?php

namespace App\Http\Controllers;

use App\Atividade;
use App\AtividadeParticipantes;
use App\AtividadePatrimonios;
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

class AtividadeControllerAPI extends Controller
{
    public function getAtividade(Request $request, $id)
    {
        if (Auth::user()->tipo == 'aluno'){
            return response()->json([
                'data' => new AtividadeResource(Atividade::findOrFail($id)),
                'estado' => AtividadeParticipantes::where('atividade_id', $id)->where('user_id', Auth::user()->id)
                    ->select('estado')->first()
            ]);
        }
        return response()->json([
            'data' => new AtividadeResource(Atividade::findOrFail($id))
        ]);
    }

    public function getTodas(Request $request, $id)
    {
        $collection = (Atividade::where('visibilidade', 'publico')->get());
        $participantes = (Atividade::join('atividade_participantes', 'atividade_id', 'id')->where('user_id', $id)->get());
        return response()->json([
            'data' => ShortAtividadeResource::collection($collection->diff($participantes))
        ]);
    }

    public function getPendentes(Request $request, $id)
    {
        return response()->json([
            'data' => ShortAtividadeResource::collection(Atividade::join('atividade_participantes', 'atividade_id', 'id')
                ->where('user_id', $id)->where('estado', 'pendente')->get())
        ]);
    }

    public function getConcluidas(Request $request, $id)
    {
        return response()->json([
            'data' => ShortAtividadeResource::collection(Atividade::join('atividade_participantes', 'atividade_id', 'id')
                ->where('user_id', $id)->where('estado', 'concluida')->get())
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

    public function minhaEscolaAtividades(){
        return ShortAtividadeResource::collection( Atividade::select('atividades.*')
            ->leftJoin('atividade_participantes', 'atividade_id', 'atividades.id')
            ->join('users','users.id','coordenador')
            ->where('escola_id',Auth::user()->escola_id)
            ->where('visibilidade','NOT LIKE','privado')->get());
    }

    public function getTipos(){
        $data = Atividade::select('tipo')->distinct()->get()->pluck('tipo')->toArray();
        array_push($data, 'visita de estudo', 'trabalho em familia', 'trabalho de pesquisa', 'definir tipos de patrimonio');
        return response()->json([
            'data' => array_values(array_unique($data))
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|min:3',
            'descricao' => 'required|min:10:max:1000',
            'tipo' => 'required',
            'numeroElementos' => 'required|numeric|digits_between:1,99',
            'visibilidade' => 'required',
            'data' => 'nullable',
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
        ]);
        if ($request->get('chatExist')) {
            $chat = new Chat();
            $chat->assunto = $request->chat['assunto'];
            $chat->privado = $request->get('visibilidade') != 'publico';
            $chat->save();
            $atividade->chat_id = $chat->id;
        }
        $atividade->save();

        if ($request->get('visibilidade') == 'privado' && $request->has('participantes')
            && sizeof($request->get('participantes')) > 0) {
            $coordenador = User::findOrFail(Auth::id());
            foreach ($request->participantes as $participante) {
                $atividadeParticipante = new AtividadeParticipantes();
                $atividadeParticipante->fill([
                    'atividade_id' => $atividade->id,
                    'user_id' => $participante['id'],
                    'estado' => 'pendente'
                ]);
                $atividadeParticipante->save();
                $this->notificacaoEEmail(User::findOrFail($participante['id']),
                    "Foi inserido(a) na atividade" . $atividade->titulo . " que está a ser coordenada pelo(a) porfessor(a) " . $coordenador->nome,
                    "Foi inserido(a) na atividade" . $atividade->titulo,
                    "<h3>Foi inserido(a) na atividade" . $atividade->titulo . "</h3><p>A atividade está a ser coordenada pelo(a) porfessor(a) " .
                    $coordenador->nome . "</p><p>A atividade consiste em: " . $atividade->descricao . "</p>");
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
            'descricao' => 'required|min:25',
            'tipo' => 'required',
            'numeroElementos' => 'required|numeric|digits_between:1,99',
            'visibilidade' => 'required',
            'data' => 'nullable',
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
            $chat->privado = $request->get('visibilidade') != 'publico';
            $chat->save();
            $atividade->chat_id = $chat->id;
        } else if ($atividade->chat_id && $request->get('chatExist')){//atualiza
            $chat = Chat::findOrFail($atividade->chat_id);
            $chat->assunto = $request->chat['assunto'];
            $chat->save();
        } else if ($atividade->chat_id && !$request->get('chatExist')) {//apaga
            $oldChatId = $atividade->chat_id;
            $atividade->chat_id = null;
            Chat::findOrFail($oldChatId)->delete();
        }
        //participantes
        if ($request->get('visibilidade') == 'privado' && sizeof($request->get('participantes')) > 0) {
            $participantesRecebidos = array_column($request->get('participantes'), 'id');
            $participantes = AtividadeParticipantes::where('atividade_id', $id)->get()->pluck('user_id')->toArray();
            $inseridos = array_diff($participantesRecebidos, $participantes);//array de id's
            foreach ($inseridos as $participanteId) {
                $atividadeParticipante = new AtividadeParticipantes();
                $atividadeParticipante->fill([
                    'atividade_id' => $id,
                    'user_id' => $participanteId,
                    'estado' => 'pendente'
                ]);
                $atividadeParticipante->save();
                $this->notificacaoEEmail(User::findOrFail($participanteId),
                    "Foi inserido(a) na atividade " . $atividade->titulo . " que está a ser coordenada pelo(a) porfessor(a) " . $coordenador->nome,
                    "Foi inserido(a) na atividade" . $atividade->titulo,
                    "<h3>Foi inserido(a) na atividade " . $atividade->titulo . "</h3><p>A atividade está a ser coordenada pelo(a) porfessor(a) " .
                    $coordenador->nome . "</p><p>A atividade consiste em: " . $atividade->descricao . "</p>");
            }
            $removidos = array_diff($participantes, $participantesRecebidos);
            foreach ($removidos as $participanteId) {
                $participante = AtividadeParticipantes::where('atividade_id', $id)->where('user_id', $participanteId);
                if ($participante->first()->estado == 'pendente') {
                    $participante->delete();
                    $this->notificacaoEEmail(User::findOrFail($participanteId),
                        "Foi removido(a) da atividade " . $atividade->titulo . " que é coordenada pelo(a) porfessor(a) " . $coordenador->nome,
                        "Foi removido(a) na atividade" . $atividade->titulo,
                        "<h3>Foi removido(a) na atividade " . $atividade->titulo . "</h3><p>A atividade é coordenada pelo(a) porfessor(a) " .
                        $coordenador->nome . "</p>");
                }
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
        $alteracoes = '';
        if ($atividade->tipo != $request->tipo){
            $alteracoes .= "passou de " . $atividade->tipo ." para " . $request->tipo . ", ";
        }
        if ($atividade->numeroElementos != $request->numeroElementos){
            $alteracoes .= "passou de grupos de " . $atividade->numeroElementos ." para grupos de " . $request->numeroElementos. ", ";
        }
        if ($atividade->data != $request->data){
            $alteracoes .= "a data limite é " . date('d-m-Y', strtotime($request->data)) . ".";
        }
        if ($alteracoes != '') {
            $ids = AtividadeParticipantes::where('atividade_id', $id)->get()->pluck('user_id')->toArray();
            foreach ($ids as $id) {
                $this->notificacaoEEmail(User::findOrFail($id),
                    "As especificações da atividade " . $request->titulo . " foram alteradas, " . $alteracoes,
                    "A atividade " . $request->titulo . " foi alterada",
                    "<h3>As especificações da atividade " . $request->titulo . " foram alteradas</h3><p>A atividade 
                    é coordenada pelo(a) porfessor(a) " . $coordenador->nome . " e passou a ter as seguintes configurações: " . 
                    $alteracoes . "</p>");
            }
        }

        $atividade->fill([
            'titulo' => $request->get('titulo'),
            'descricao' => $request->get('descricao'),
            'tipo' => $request->get('tipo'),
            'numeroElementos' => $request->get('numeroElementos'),
            'visibilidade' => $request->get('visibilidade'),
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

    public function storeParticipante(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required',
        ]);

        User::findOrFail($request->user_id);
        Atividade::findOrFail($id);

        if (Auth::id() != $request->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $participante = new AtividadeParticipantes();
        $participante->fill([
            'atividade_id' => $id,
            'user_id' => $request->user_id,
            'estado' => 'pendente'
        ]);
        $participante->save();

        return response()->json($participante, 201);
    }
}
