<?php

namespace App\Http\Controllers;

use App\Escola;
use App\Turma;
use App\User;
use Illuminate\Http\Request;
use App\Http\Resources\Escola as EscolaResource;
use App\Http\Resources\Turma as TurmaResource;

class EscolaControllerAPI extends Controller
{
    public function escolas(Request $request)
    {
        $escolas = EscolaResource::collection(Escola::all());
        return response()->json([
            'data' => $escolas,
        ]);
    }


    public function store(Request $request)
    {

        $request->validate([
            'nome' => 'required|min:3|max:255',
            'distrito' => 'required',
        ]);

        $escola = new Escola();
        $escola->fill($request->all());
        $escola->save();

        return response()->json(new EscolaResource($escola), 201);

    }

    public function criarTurma($id, Request $request)
    {
        $request->validate([
            'nome' => 'required|min:1|max:9',
            'ciclo' => 'required',
            'professor' => 'nullable',
        ]);

        $escola = Escola::findOrFail($id);

        if (Turma::where('escola_id', $id)->where('nome', $request->input('nome'))->first()){
            return response()->json("Erro: Nome ja existente", 500);
        }

        $turma = new Turma();
        $turma->fill($request->all());
        $turma->ciclo = $request->input('ciclo');

        if ($request->has('professor') && $request->input('professor') != "") {
            $professor = User::where('nome', $request->input('professor'))->first();
            if (!$professor || $professor->tipo != 'professor') {
                return response()->json("Professor Invalido", 500);
            }
            $turma->professor_id = $professor->id;
        }
        $turma->escola_id = $escola->id;

        $turma->save();

        return response()->json(new TurmaResource($turma), 201);

    }

    public function editarTurma($id, Request $request)
    {

        $request->validate([
            'nome' => 'required|min:1|max:9',
            'ciclo' => 'required',
            'professor' => 'nullable',
        ]);

        $turma = Turma::findOrFail($id);

        if ($request->input('nome') != $turma->nome) {
            if (Turma::where('escola_id', $turma->escola_id)->where('nome', $request->input('nome'))->first()) {
                return response()->json("Erro: Nome ja existente", 500);
            }
            $turma->nome = $request->input('nome');
        }

        $turma->ciclo = $request->input('ciclo');

        if ($request->has('professor') && $request->input('professor') != "") {
            $professor = User::where('nome', $request->input('professor'))->first();
            if (!$professor || $professor->tipo != 'professor') {
                return response()->json("Professor Invalido", 500);
            }
            $turma->professor_id = $professor->id;
        }

        $turma->save();

        return response()->json(new TurmaResource($turma), 201);

    }

    public function destroy($id)
    {
        $escola = Escola::findOrFail($id);
        $escola->delete();
        return response()->json(null, 201);
    }

    public function destroyTurma($id)
    {
        $turma = Turma::findOrFail($id);
        $turma->delete();
        return response()->json(null, 201);
    }

}
