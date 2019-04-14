<?php

namespace App\Http\Controllers;

use App\Atividade;
use App\AtividadeParticipantes;
use App\Http\Resources\Atividade as AtividadeResource;
use Illuminate\Http\Request;

class AtividadeControllerAPI extends Controller
{
    public function getTodas(Request $request)
    {
        return response()->json([
            'data' => AtividadeResource::collection(Atividade::where('privado', '0')->get())
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
            'data' => AtividadeResource::collection(AtividadeParticipantes::where('user_id', $id)
                ->where('estado', 'concluida')->get())
        ]);
    }


}
