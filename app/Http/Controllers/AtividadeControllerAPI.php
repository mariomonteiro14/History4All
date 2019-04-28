<?php

namespace App\Http\Controllers;

use App\Atividade;
use App\AtividadeParticipantes;
use App\Http\Resources\Atividade as AtividadeResource;
use App\Http\Resources\User as UserResources;
use App\User;
use Illuminate\Http\Request;

class AtividadeControllerAPI extends Controller
{
    public function getTodas(Request $request, $id)
    {
        $collection = (Atividade::where('privado', '0')->get());
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
                'data' => AtividadeResource::collection(Atividade::where('coordenador',$id)->get())

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


}
