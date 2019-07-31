<?php

namespace App\Http\Controllers;

use App\Comentario;
use App\Forum;
use App\ForumPatrimonios;
use App\HistoricoGerenciadorCodigos;
use App\Http\Resources\Comentario as ComentarioResource;
use App\Http\Resources\Forum as ForumResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use App\Mail\MensagemEmail;
use Illuminate\Support\Carbon;

class ForumControllerAPI extends Controller
{
    public function forums(Request $request)
    {
        $forums = ForumResource::collection(Forum::all());
        return response()->json([
            'data' => $forums,
        ]);
    }

    public function comments($id, Request $request){

        $forum = ForumResource::findOrFail($id);
        return response()->json(ComentarioResource::collection($forum->comentarios()->get()), 200);
    }


    public function storeForum(Request $request)
    {
        $request->showEmail = filter_var($request->showEmail, FILTER_VALIDATE_BOOLEAN);
        $request->validate([
                'titulo' => 'required|string',
                'descricao' => 'required|string',
                'userEmail' => 'required|email',
                'showEmail' => 'required',
                'patrimonios' => 'required'
            ]);

        if (!$request->user('api')){
            if (!$request->has("codigo")){
               return abort(400, "O email não foi verificado");
            }
            if(!HistoricoGerenciadorCodigos::where('email', $request->userEmail)->where('codigo', $request->codigo)->first()){
                return abort(403, "Codigo introduzido invalido!");
            }
        }

        $forum = new Forum([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'user_email' => $request->userEmail,
            'show_email' => $request->showEmail ? 1 : 0
        ]);
        $forum ->save();

        foreach ($request->patrimonios as $pat){
            $forumPat = new ForumPatrimonios();
            $forumPat->fill([
                'forum_id' => $forum->id,
                'patrimonio_id' => $pat['id'],
            ]);
            $forumPat->save();
        }

        return response()->json(new ForumResource($forum), 201);
    }

    public function storeComment($id, Request $request)
    {
        $request->validate([
            'resposta' => 'required|string',
            'userEmail' => 'required|email',
        ]);
        $forum = Forum::findOrFail($id);

        $comment = new Comentario();

        $comment->fill([
        'forum_id' => $forum->id,
        'resposta' => $request->resposta,
        'user_email' => $request->userEmail,
    ]);
        return response()->json(new ComentarioResource($comment), 201);

    }

    public function generateAccessCode(Request $request){ //funçao para verificar email na criaçao/ediçao do forum ou comentario
        $request->validate([
            'userEmail' => 'required|email']
        );
        // Random numero de 6 digitos
        $random = intval( "0" . rand(1,9) . rand(1,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) );

        $novoRegisto = new HistoricoGerenciadorCodigos();
        $novoRegisto->fill([
            'email' => $request->userEmail,
            'codigo' => $random,
            'data' => new Carbon()
        ]);
        $novoRegisto->save();

        //enviar email com $random para $request->email
        Mail::to($request->userEmail)->send(new MensagemEmail(null, 'Código de acesso do History4All', 
            '<p>Use o seguinte código para poder criar e editar os seus fórums e poder comentar neles: <b>' . $random . '</b>'));
        return response()->json(null, 200);
    }

    public function updateForum ($id, Request $request){

    }

    public function updateComment ($id, Request $request){

    }

    public function incrementLike($id, Request $request){
        $comment = Comentario::findOrFail($id);
        $comment->likes = $comment->likes + 1;
        $comment->save();

        return response()->json(null, 200);
    }
    public function decrementLike($id, Request $request){
        $comment = Comentario::findOrFail($id);
        $comment->likes = $comment->likes - 1;
        $comment->save();

        return response()->json(null, 200);
    }

    public function incrementDislike($id, Request $request){
        $comment = Comentario::findOrFail($id);
        $comment->dislikes = $comment->dislikes + 1;
        $comment->save();

        return response()->json(null, 200);
    }

    public function decrementDislike($id, Request $request){
        $comment = Comentario::findOrFail($id);
        $comment->dislikes = $comment->dislikes - 1;
        $comment->save();

        return response()->json(null, 200);
    }

    public function destroyForum($id, Request $request)
    {
        $forum = Forum::findOrFail($id);

        if (!Auth::user()){
            if (!$request->has("eamil") || !$request->has("codigo") ){
                return abort(400,"necessita de provar ser o criador do forum");
            }
            if(!HistoricoGerenciadorCodigos::where('email', $request->email)->where('codigo', $request->codigo)->first()){
                return abort(401,"Codigo introduzido invalido!");
            }
        }else{
            if ($forum->user_email != Auth::user()->email || Auth::user()->tipo != 'admin'){
                return abort(403, "nao tens permissoes para eliminar este forum");
            }
        }

        $forum->delete();
        return response()->json(null, 201);
    }

    public function destroyComment($id, Request $request)
    {
        $comment = Comentario::findOrFail($id);

        if (!Auth::user()){
            if (!$request->has("eamil") || !$request->has("codigo") ){
                return abort(400,"necessita de provar ser o criador do forum");
            }
            if(!HistoricoGerenciadorCodigos::where('email', $request->email)->where('codigo', $request->codigo)->first()){
                return abort(401,"Codigo introduzido invalido!");
            }
        }else{
            if ($comment->user_email != Auth::user()->email || Auth::user()->tipo != 'admin'){
                return abort(403, "nao tens permissoes para eliminar este forum");
            }
        }

        $comment->delete();
        return response()->json(null, 201);
    }

}
