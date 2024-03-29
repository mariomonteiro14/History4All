<?php

namespace App\Http\Controllers;

use App\Chat;
use App\Escola;
use App\Http\Resources\EscolaEstatisticas;
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
            'data' => new TurmaResource(Turma::findOrFail($id)),
        ]);
    }

    public function myEscola(Request $request){

        $escola = Escola::findOrFail(Auth::user()->escola_id);
        return response()->json([
            'data' => new EscolaResource($escola),
        ]);
    }

    public function myTurma(Request $request){

        $turma = Turma::findOrFail(Auth::user()->turma_id);
        return response()->json([
            'data' => new TurmaResource($turma),
        ]);
    }

    public function myEscolaEstatisticas(Request $request){

        $escola = Auth::user()->escola()->first();
        return response()->json([
            'data' => new EscolaEstatisticas($escola),
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
            'distrito' => 'required|string|in:Aveiro,Beja,Braga,Bragança,Castelo Branco,Coimbra,Évora,Faro,Guarda,Leiria,Lisboa,Portalegre,Porto,Santarém,Setúbal,Viana do Castelo,Vila Real,Viseu,Açores,Madeira',
        ]);

        $escola = new Escola();
        $escola->fill($request->all());

        $chat = new Chat();
        $chat->assunto = "Chat dos professores";
        $chat->save();

        $escola->chat_professores_id = $chat->id;
        $escola->save();

        return response()->json(new EscolaResource($escola), 201);

    }

    public function criarTurma($id, Request $request)
    {
        $request->validate([
            'nome' => 'required|min:1|max:9',
            'ciclo' => 'required|string|in:1º ciclo,2º ciclo,3º ciclo,secundário',
            'professor' => 'nullable|email',
        ]);

        $escola = Escola::findOrFail($id);

        if (Turma::where('escola_id', $id)->where('nome', $request->input('nome'))->first()){
            return response()->json(['message' => 'Nome já existente'], 409);
        }

        $turma = new Turma();
        $turma->fill($request->all());
        $turma->ciclo = $request->input('ciclo');

        if ($request->has('professor') && $request->input('professor') != "") {
            $professor = User::where('email', $request->input('professor'))->first();
            if (!$professor || $professor->tipo != 'professor') {
                return response()->json(['message' => 'Professor Invalido'], 400);
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
            'ciclo' => 'required|string|in:1º ciclo,2º ciclo,3º ciclo,secundário',
            'professor' => 'nullable|email',
        ]);

        $turma = Turma::findOrFail($id);
        if (Auth::user()->tipo == "professor" && $turma->professor_id != Auth::id())
        {
            abort(403, 'Unauthorized action.');
        }

        if ($request->input('nome') != $turma->nome) {
            if (Turma::where('escola_id', $turma->escola_id)->where('nome', $request->input('nome'))->first()) {
                return response()->json(['message' => 'Nome já existente'], 409);
            }
            $turma->nome = $request->input('nome');
        }

        $turma->ciclo = $request->input('ciclo');

        if ($request->has('professor') && $request->input('professor') != "") {
            $professor = User::where('email', $request->input('professor'))->first();

            if (!$professor || $professor->tipo != 'professor') {
                return response()->json(['message' => 'Professor Invalido'], 400);
            }
            $turma->professor_id = $professor->id;
        } elseif ($turma->professor_id != null){//se tinha professor e foi removido
            $turma->professor_id = null;
        }

        if($request->has('alunos') && (sizeof($request->input('alunos')) > 0 || count(User::where('turma_id', $turma->id)->get()) > 0)){
            $alunosId = array_column($request->alunos, 'id');

            $notificacaoMensagem = "Foi removido(a) da turma" . $turma->nome . " estando de momento sem turma associada";
            $assunto = "Foi desassociado(a) da sua turma";
            $emailMensagem = "<h3>Foi removido(a) da sua turma</h3><p>Já não está na turma " . $turma->nome . ". De momento não tem turma associada 
                no <a href='http://142.93.219.146/'>History4All</a></p>";

            foreach (User::where('turma_id', $turma->id)->get() as $aluno){
                if (!in_array($aluno->id, $alunosId)){//se foi removido
                    $aluno->turma_id = null;
                    $aluno->save();
                    $this->notificacaoEEmail($aluno, $notificacaoMensagem, $assunto, $emailMensagem, null);
                }
            }
            $alunos = User::where('turma_id', $turma->id)->get()->pluck('id')->toArray();

            $notificacaoMensagem = "Foi movido(a) para a turma " . $turma->nome . " que é lecionada pelo(a) professor(a) " . $professor->nome;
            $assunto = "A sua turma foi alterada";
            $emailMensagem = "<h3>Foi movido(a) para a turma</h3><p>A sua turma passou a ser a " . $turma->nome .
                " que é lecionada pelo(a) professor(a) " . $professor->nome . "no <a href='http://142.93.219.146/'>History4All</a></p>";
            $link = "alunos/escola";

            foreach ($request->alunos as $aluno){
                if (!in_array($aluno['id'], $alunos)) {//se foi inserid
                    $aluno = User::findOrFail($aluno['id']);
                    $aluno->turma_id = $turma->id;
                    $aluno->save();
                    $this->notificacaoEEmail($aluno, $notificacaoMensagem, $assunto, $emailMensagem, $link);
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
