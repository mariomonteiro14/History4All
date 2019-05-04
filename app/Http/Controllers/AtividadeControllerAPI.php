<?php

namespace App\Http\Controllers;

use App\Atividade;
use App\AtividadeParticipantes;
use App\AtividadePatrimonios;
use App\Chat;
use App\Http\Resources\Atividade as AtividadeResource;
use App\Http\Resources\User as UserResources;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AtividadeControllerAPI extends Controller
{
    public function getTodas(Request $request, $id)
    {
        $collection = (Atividade::where('visibilidade', 'publico')->get());
        $participantes = (Atividade::join('atividade_participantes', 'atividade_id', 'id')->where('user_id', $id)->get());
        return response()->json([
                'data' => AtividadeResource::collection($collection->diff($participantes))
        ]);
    }

    public function getParticipadas(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($user->tipo == "professor"){
            return response()->json([
                'data' => AtividadeResource::collection(Atividade::join('atividade_participantes', 'atividade_id', 'id')
                    ->where('user_id', $id)->get())
            ]);
        }

        return response()->json([
            'data' => AtividadeResource::collection(Atividade::join('atividade_participantes', 'atividade_id', 'id')
                ->where('user_id', $id)->get())
        ]);
    }

    public function getPendentes(Request $request, $id)
    {
        return response()->json([
            'data' => AtividadeResource::collection(Atividade::join('atividade_participantes', 'atividade_id', 'id')
                ->where('user_id', $id)->where('estado', 'pendente')->get())
        ]);
    }

    public function getConcluidas(Request $request, $id)
    {
        return response()->json([
            'data' => AtividadeResource::collection(Atividade::join('atividade_participantes', 'atividade_id', 'id')
                ->where('user_id', $id)->where('estado', 'concluida')->get())
        ]);
    }

    public function getMinhas(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($user->tipo == "professor"){
            return response()->json([
                'data' => AtividadeResource::collection(Atividade::where('coordenador',$id)->get())
            ]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|min:3',
            'descricao' => 'required|min:3',
            'tipo' => 'required',
            'numeroElementos' => 'required|numeric|digits_between:1,99',
            'visibilidade' => 'required',
            'data' => 'nullable',
            'chat.assunto' => 'nullable|min:3',
        ]);

        $atividade = new Atividade();
        $atividade->fill([
            'titulo' => $request->get('titulo'),
            'descricao' => $request->get('descricao'),
            'tipo' => $request->get('tipo'),
            'numeroElementos' => $request->get('numeroElementos'),
            'visibilidade' => $request->get('visibilidade'),
            'coordenador' => Auth::id(),
        ]);
        if ($request->get('chatExist')){
            $chat = new Chat();
            $chat->assunto = $request->get('chat.assunto');
            $chat->privado = $request->get('visibilidade') != 'publico';
            $chat->save();
            $atividade->chat_id = $chat->id;
        }
        $atividade->save();

        if($request->get('visibilidade') == 'privado' && $request->has('participantes')
            && sizeof($request->get('participantes')) > 0){
            foreach ($request->get('participantes') as $participante){
                $atividadeParticipante = new AtividadeParticipantes();
                $atividadeParticipante->fill([
                    'atividade_id' => $atividade->id,
                    'user_id' => $participante['id'],
                    'estado' => 'pendente'
                ]);
                $atividadeParticipante->save();
            }
        }

        if($request->has('patrimonios') && sizeof($request->get('patrimonios')) > 0){
            foreach ($request->get('patrimonios') as $patrimonio){
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

    }

    public function destroy($id)
    {
        $atividade = Atividade::findOrFail($id);
        $atividade->delete();
        return response()->json(null, 201);
    }
}
