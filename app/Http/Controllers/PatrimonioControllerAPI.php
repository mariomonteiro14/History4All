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

    public function update(Request $request, $id){
        $patrim = Patrimonio::findOrFail($id);
        $patrim->fill($request->all());
        $patrim->save();
        return new PatrimonioResource($patrim);
    }

    public function store(Request $request){
        $patrim = new Patrimonio();
        $patrim->fill($request->all());
        $patrim->save();
        return response()->json(new PatrimonioResource($patrim), 201);
    }
}
