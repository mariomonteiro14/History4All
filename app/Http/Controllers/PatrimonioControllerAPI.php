<?php

namespace App\Http\Controllers;

use App\Patrimonio;
use App\PatrimonioImagens;
use Illuminate\Http\Request;
use App\Http\Resources\Patrimonio as PatrimonioResource;

class PatrimonioControllerAPI extends Controller
{
    public function patrimoniosDataTable(Request $request)
    {
        $patrim = PatrimonioResource::collection(Patrimonio::all());
        /*foreach ($patrim as $patr){
            $patr['foto'] = PatrimonioImagens::where('patrimonio_id', $patr->id)->first()->foto;
        }*/
        return response()->json([
            'data' => $patrim,
        ]);
    }

    public function find(Request $request, $id){
        return Patrimonio::with('imagens')->where('id', $id)->first();
    }

}
