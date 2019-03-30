<?php

namespace App\Http\Controllers;

use App\Patrimonio;
use Illuminate\Http\Request;
use App\Http\Resources\Patrimonio as PatrimonioResource;

class PatrimonioControllerAPI extends Controller
{
    public function patrimoniosDataTable(Request $request)
    {
        $patrim = PatrimonioResource::collection(Patrimonio::all());
        return response()->json([
            'data' => $patrim,
        ]);
    }

    public function find(Request $request, $id){
        return Patrimonio::with('imagens')->where('id', $id)->first();
    }

}
