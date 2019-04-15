<?php

namespace App\Http\Controllers;

use App\Escola;
use Illuminate\Http\Request;
use App\Http\Resources\Escola as EscolaResource;

class EscolaControllerAPI extends Controller
{
    public function escolas(Request $request)
    {
        $escolas = EscolaResource::collection(Escola::all());
        return response()->json([
            'data' => $escolas,
        ]);
    }


    public function store(Request $request){

        $request->validate([
            'nome' => 'required|min:3|max:255',
            'distrito' => 'required',
        ]);

        $escola = new Escola();
        $escola->fill($request->all());
        $escola->save();

        return response()->json(new EscolaResource($escola), 201);

    }

    public function destroy($id)
    {
        $escola = Escola::findOrFail($id);
        $escola->delete();
        return response()->json(null, 201);
    }

}
