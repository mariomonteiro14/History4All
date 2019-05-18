<?php

namespace App\Http\Controllers;

use App\Escola;
use App\Turma;
use App\User;
use Illuminate\Http\Request;
use App\Http\Resources\Escola as EscolaResource;
use App\Http\Resources\EscolaAdmin as EscolaAdminResource;
use App\Http\Resources\Turma as TurmaResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User as UserResource;

class EscolaControllerAPI extends Controller
{
    public function escolas(Request $request)
    {
        $escolas = EscolaAdminResource::collection(Escola::all());
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

    public function myEscolaAlunos(Request $request)
    {
        $users = UserResource::collection(User::where('tipo', 'aluno')
            ->where('escola_id',Auth::user()->escola_id)
            ->orderBy('nome')
            ->get()
        );
        return response()->json([
            'data' => $users,
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
            'professor' => 'nullable|email',
        ]);

        $escola = Escola::findOrFail($id);

        if (Turma::where('escola_id', $id)->where('nome', $request->input('nome'))->first()){
            return response()->json("Erro: Nome ja existente", 500);
        }

        $turma = new Turma();
        $turma->fill($request->all());
        $turma->ciclo = $request->input('ciclo');

        if ($request->has('professor') && $request->input('professor') != "") {
            $professor = User::where('email', $request->input('professor'))->first();
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
            'professor' => 'nullable|email',
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

        $professor = User::where('email', $request->input('professor'))->first();
        if ($request->has('professor') && $request->input('professor') != "") {
            if (!$professor || $professor->tipo != 'professor') {
                return response()->json("Professor Invalido", 500);
            }
            $turma->professor_id = $professor->id;
        } elseif ($turma->professor_id != null){//se tinha professor e foi removido
            $turma->professor_id = null;
        }

        if($request->has('alunos') && sizeof($request->alunos) > 0){
            $alunosId = array_column($request->alunos, 'id');
            foreach (User::where('turma_id', $turma->id)->get() as $aluno){
                if (!in_array($aluno->id, $alunosId)){//se foi removido
                    $aluno->turma_id = null;
                    $aluno->save();
                    $this->notificacaoEEmail($aluno, 
                        "Foi removido(a) da turma" . $turma->nome . " estando de momento sem turma associada",
                        "Ficou se turma associada",
                        "<h3>Foi removido(a) da sua turma</h3><p>Já não está na turma " . $turma->nome . ". De momento não tem turma associada 
                        no <a href='http://142.93.219.146/'>History4All</a></p>");
                }
            }
            $alunos = User::where('turma_id', $turma->id)->get()->pluck('id')->toArray();
            foreach ($request->alunos as $aluno){
                if (!in_array($aluno['id'], $alunos)) {//se foi inserid
                    $aluno = User::findOrFail($aluno['id']);
                    $aluno->turma_id = $turma->id;
                    $aluno->save();
                    $this->notificacaoEEmail($aluno, 
                        "Foi movido(a) para a turma " . $turma->nome . " que é lecionada pelo(a) professor(a) " . $professor->nome, 
                        "A sua turma foi alterada",
                        "<h3>Foi movido(a) para a turma</h3><p>A sua turma passou a ser a " . $turma->nome . 
                        " que é lecionada pelo(a) professor(a) " . $professor->nome . "no <a href='http://142.93.219.146/'>History4All</a></p>");
                }
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
