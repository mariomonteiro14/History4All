<?php

namespace App\Http\Controllers;

use App\Escola;
use App\Turma;
use App\User;
use Illuminate\Http\Request;
use App\Http\Resources\Escola as EscolaResource;
use App\Http\Resources\Turma as TurmaResource;
use Illuminate\Support\Facades\Auth;

class EscolaControllerAPI extends Controller
{
    public function escolas(Request $request)
    {
        $escolas = EscolaResource::collection(Escola::all());
        return response()->json([
            'data' => $escolas,
        ]);
    }
    public function escolaTurmas($id, Request $request){

        return response()->json([
            'data' => TurmaResource::collection(Turma::findOrFail($id)),
        ]);
    }

    public function myEscola(Request $request){

        $escola = Escola::findOrFail(Auth::user()->escola_id);
        return response()->json([
            'data' => new EscolaResource($escola),
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

        if ($request->has('professor') && $request->has('professor.email') && $request->input('professor.email') != "") {
            $professor = User::where('email', $request->input('professor.email'))->first();
            if (!$professor || $professor->tipo != 'professor') {
                return response()->json("Professor Invalido", 500);
            }
            $turma->professor_id = $professor->id;
        }
        $turma->escola_id = $escola->id;

        $turma->save();

        if($request->has('alunos') && sizeof($request->alunos) > 0){
            foreach ($request->alunos as $aluno){
                $aluno = User::findOrFail($aluno['id']);
                $aluno->turma_id = $turma->id;
                $aluno->save();
            }
        }



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
        if (Auth::user()->tipo == "professor" && $turma->professor_id != Auth::id())
        {
            abort(403, 'Unauthorized action.');
        }

        if ($request->input('nome') != $turma->nome) {
            if (Turma::where('escola_id', $turma->escola_id)->where('nome', $request->input('nome'))->first()) {
                return response()->json("Erro: Nome ja existente", 500);
            }
            $turma->nome = $request->input('nome');
        }

        $turma->ciclo = $request->input('ciclo');
        if ($request->has('professor') && $request->has('professor.email') && $request->input('professor.email') != "") {
            $professor = User::where('email', $request->input('professor.email'))->first();
            if (!$professor || $professor->tipo != 'professor') {
                return response()->json("Professor Invalido", 500);
            }
            $turma->professor_id = $professor->id;
        }

        if($request->has('alunos') && sizeof($request->alunos) > 0){
            foreach (User::where('turma_id', $turma->id)->get() as $aluno){
                $aluno->turma_id = null;
                $aluno->save();
            }

            foreach ($request->alunos as $aluno){
                $aluno = User::findOrFail($aluno['id']);
                $aluno->turma_id = $turma->id;
                $aluno->save();
            }
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
