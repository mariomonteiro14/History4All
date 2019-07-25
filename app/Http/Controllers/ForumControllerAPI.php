<?php

namespace App\Http\Controllers;

use App\Comentario;
use App\Forum;
use App\ForumPatrimonios;
use App\Http\Resources\Comentario as ComentarioResource;
use App\Http\Resources\Forum as ForumResource;
use Illuminate\Http\Request;

class ForumControllerAPI extends Controller
{
    public function forums(Request $request)
    {
        $forums = Forum::collection(Forum::all());
        return response()->json([
            'data' => $forums,
        ]);
    }

    public function comments($id, Request $request){

        $forum = Forum::findOrFail($id);
        return response()->json(ComentarioResource::collection($forum->comentarios()->get()), 200);
    }


    public function storeForum(Request $request)
    {
        $request->validate([
                'assunto' => 'required|string',
                'userEmail' => 'required|email',
                'showEmail' => 'required|boolean',
                'patrimonios' => 'required'
            ]);

        $forum = new Forum([
            'assunto' => $request->assunto,
            'user_email' => $request->userEmail,
            'show_email' => $request->showEmail ? 1 : 0,

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
            'email' => 'required|email']
        );
        // Random numero de 6 digitos
        $random = intval( "0" . rand(1,9) . rand(1,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) );

        //enviar email com $random para $request->email

        return response()->json([
            'data' => $random,
        ]);
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

    public function destroyForum($id)
    {
        $forum = Forum::findOrFail($id);
        $forum->delete();
        return response()->json(null, 201);
    }

    public function destroyComment($id)
    {
        $comment = Comentario::findOrFail($id);
        $comment->delete();
        return response()->json(null, 201);
    }

}
