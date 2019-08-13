<?php

namespace App\Http\Controllers;

use App\Comentario;
use App\Forum;
use App\Notificacao;
use App\User;
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
use Illuminate\Support\Facades\DB;

class ForumControllerAPI extends Controller
{
    public function forums(Request $request)
    {
        $forums = ForumResource::collection(Forum::all());
        return response()->json([
            'data' => $forums,
        ]);
    }

    public function forum($id, Request $request)
    {
        $forum = Forum::findOrFail($id);
        return response()->json([
            'data' => new ForumResource($forum)
        ]);
    }

    public function comments($id, Request $request)
    {

        $forum = Forum::findOrFail($id);
        return response()->json(['data' => ComentarioResource::collection($forum->comentarios()->orderByRaw('(likes - dislikes) DESC')->get())]);
    }


    public function storeForum(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'descricao' => 'required|string',
            'user_email' => 'required|email',
            'patrimonios' => 'required'
        ]);

        if (!$request->user('api')) {
            if (!$request->has("codigo")) {
                return abort(400, "O email não foi verificado");
            }
            if (!HistoricoGerenciadorCodigos::where('email', $request->user_email)->where('codigo', $request->codigo)->first()) {
                return abort(403, "Codigo introduzido invalido!");
            }
        }

        $forum = new Forum([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'user_email' => $request->user_email
        ]);
        $forum->save();

        foreach ($request->patrimonios as $pat) {
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
            'comentario' => 'required|string',
            'userEmail' => 'nullable|email',
        ]);
        $forum = Forum::findOrFail($id);

        $comment = new Comentario();


        if (!$request->user('api') && $request->has('userEmail')) {
            //confirmar codigo
            if(!$request->has('codigo')){
                return abort(403, "Necessario verificar email");
            }
            if (!HistoricoGerenciadorCodigos::where('email', $request->userEmail)->where('codigo', $request->codigo)->first()) {
                return abort(403, "Codigo introduzido invalido!");
            }
        }

        $comment->fill([
            'forum_id' => $forum->id,
            'comentario' => $request->comentario,
            'user_email' => $request->has('userEmail') ? $request->userEmail : null,
        ]);
        $comment->save();

        Mail::to($forum->user_email)->send(new MensagemEmail(null, 'Novo comentario dum forum do History4All',
            '<p>Foi adicionado um novo comentario no forum:'.$forum->titulo.' (criado por si).</p><br><a href="http://142.93.219.146/forums/'.$forum->id.'">Ir para '.$forum->titulo.'</a>' ));

        return response()->json(new ComentarioResource($comment), 201);

    }

    public function compararCodigo(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string',
            'user_email' => 'required|email',
        ]);

        if (!HistoricoGerenciadorCodigos::where('email', $request->user_email)->where('codigo', $request->codigo)->first()) {
            return abort(403, "Codigo introduzido invalido!");
        }
        return response()->json(null, 200);
    }

    public function generateAccessCode(Request $request)
    { //funçao para verificar email na criaçao/ediçao do forum ou comentario
        $request->validate([
                'user_email' => 'required|email']
        );
        // Random numero de 6 digitos
        $random = intval("0" . rand(1, 9) . rand(1, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9));

        $novoRegisto = new HistoricoGerenciadorCodigos();
        $novoRegisto->fill([
            'email' => $request->user_email,
            'codigo' => $random,
            'data' => new Carbon()
        ]);

        //enviar email com $random para $request->email
        Mail::to($request->user_email)->send(new MensagemEmail(null, 'Código de acesso do History4All',
            '<p>Use o seguinte código para poder criar e editar os seus fórums e poder comentar neles: <b>' . $random . '</b></p>'));

        //Eliminar todos os registos anteriores associados a este email.
        HistoricoGerenciadorCodigos::where('email', $request->user_email)->delete();
        $novoRegisto->save();

        return response()->json(null, 200);
    }

    public function compararEmails(Request $request,$tipo, $id)
    {
        $request->validate([
                'user_email' => 'required|email']
        );
        if ($tipo == "forums") {
            $forum = Forum::findOrFail($id);
            if ($forum->user_email != $request->user_email) {
                if (!$request->user('api')) {
                    return abort(403, "O email inserido não corresponde ao email que criou o fórum");
                }
                if ($request->user('api')->tipo == 'admin' && $request->has('tipo') && $request->tipo == 'eliminar') {
                    return response()->json(null, 200);
                }
                return abort(403, "Esse fórum não foi criado por si");
            }
            if ($request->user('api') && $request->user('api')->tipo == 'admin' && $request->has('tipo') && $request->tipo == 'eliminar') {
                return response()->json(['tipo' => 'forumDoAdmin'], 200);
            }
            if (!$request->user('api') && !$request->codigo) {
                return $this->generateAccessCode($request);
            }
            return response()->json(null, 200);
        }
        else{
            $comment = Comentario::findOrFail($id);
            if ($request->user('api')){
                $user = User::findOrFail($request->user('api')->id);
                if ($user->tipo == "admin" || $user->email == $comment->user_email){
                    return response()->json(null, 200);
                }else{
                    return abort(403, "Esse comentario não foi criado por si");

                }
            }
            if ($comment->user_email == null){
                return abort(400, "Este comentario nao tem email associado");
            }
            if ($comment->user_email != $request->user_email){
                return abort(403, "O email inserido nao correspode ao do criador deste comentario");
            }

            if ($request->has('generateCode') && $request->generateCode == true) {
                return $this->generateAccessCode($request);
            }
            return response()->json(null, 200);
        }
    }

    public function updateForum($id, Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'descricao' => 'required|string',
            'user_email' => 'required|email',
            'patrimonios' => 'required'
        ]);

        if (!$request->user('api')) {
            if (!$request->has("codigo")) {
                return abort(400, "O email não foi verificado");
            }
            if (!HistoricoGerenciadorCodigos::where('email', $request->user_email)->where('codigo', $request->codigo)->first()) {
                return abort(403, "Codigo introduzido invalido!");
            }
        }
        $forum = Forum::findOrFail($id);
        $forum->fill([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'user_email' => $request->user_email
        ]);
        $forum->save();

        if ($request->has('patrimonios') && sizeof($request->get('patrimonios')) > 0) {
            $patrimoniosRecebidos = array_column($request->get('patrimonios'), 'id');
            $patrimonios = ForumPatrimonios::where('forum_id', $id)->get()->pluck('patrimonio_id')->toArray();
            $inseridos = array_diff($patrimoniosRecebidos, $patrimonios);
            foreach ($inseridos as $patrimonioId) {
                $patrimonio = new ForumPatrimonios();
                $patrimonio->fill([
                    'forum_id' => $id,
                    'patrimonio_id' => $patrimonioId,
                ]);
                $patrimonio->save();
            }
            $removidos = array_diff($patrimonios, $patrimoniosRecebidos);
            if (count($request->get('patrimonios')) != ForumPatrimonios::where('forum_id', $id)->count()) {
                foreach ($removidos as $patrimonioId) {
                    ForumPatrimonios::where('forum_id', $id)->where('patrimonio_id', $patrimonioId)->delete();
                }
            }
        }

        return response()->json(new ForumResource($forum), 200);
    }

    public function updateComment($id, Request $request)
    {
        $request->validate(['comentario' => 'required|string']);

        $comment = Comentario::findOrFail($id);

        if (!$request->user('api')) {
            if (!$request->has("email") || !$request->has("codigo")) {
                return abort(400, "necessita de provar ser o criador do comentário");
            }
            if ($request->email != $comment->user_email){
                return abort(403, "Operação negada! O utilizador não é o criador deste comentário");
            }
            if (!HistoricoGerenciadorCodigos::where('email', $request->email)->where('codigo', $request->codigo)->first()) {
                return abort(401, "Código introduzido invalido!");
            }
        } else {
            $user = User::findOrFail($request->user('api')->id);
            if ($comment->user_email != $user->email && $user->tipo != 'admin') {
                return abort(403, "não tens permissões para eliminar este comentário");
            }
        }
        $comment->comentario = $request->comentario;
        $comment->save();
        return response()->json($comment, 200);
    }

    public function incrementLike($id, Request $request)
    {
        $comment = Comentario::findOrFail($id);
        $comment->likes = $comment->likes + 1;
        $comment->save();

        return response()->json(null, 200);
    }

    public function decrementLike($id, Request $request)
    {
        $comment = Comentario::findOrFail($id);
        $comment->likes = $comment->likes - 1;
        $comment->save();

        return response()->json(null, 200);
    }

    public function incrementDislike($id, Request $request)
    {
        $comment = Comentario::findOrFail($id);
        $comment->dislikes = $comment->dislikes + 1;
        $comment->save();

        return response()->json(null, 200);
    }

    public function decrementDislike($id, Request $request)
    {
        $comment = Comentario::findOrFail($id);
        $comment->dislikes = $comment->dislikes - 1;
        $comment->save();

        return response()->json(null, 200);
    }

    public function destroyForum(Request $request, $id)
    {
        $forum = Forum::findOrFail($id);
        if (!$request->user('api')) {
            if (!$request->has("user_email") || !$request->has("codigo")) {
                return abort(400, "necessita de provar ser o criador do fórum");
            }
            if ($request->user_email != $forum->user_email){
                return abort(403, "Operação negada! O utilizador não é o criador deste fórum");
            }
            if (!HistoricoGerenciadorCodigos::where('email', $request->user_email)->where('codigo', $request->codigo)->first()) {
                return abort(401, "Código introduzido invalido!");
            }
        } else {
            $user = User::findOrFail($request->user('api')->id);
            if ($forum->user_email != $user->email && $user->tipo != 'admin') {
                return abort(403, "Não tens permissões para eliminar este fórum");
            }
        }

        if (isset($user) && $user->tipo == 'admin' && $forum->user_email != $user->email) {
            $mensagem = '<p>O seu fórum ' . $forum->titulo . ' foi apagado por um dos adminsistradores do site</p>';
            if ($request->has('justificaçao')) {
                $mensagem = '<p>O seu fórum <b>' . $forum->titulo . '</b> foi apagado por um dos adminsistradores do site com a seguinte justificação:</p><p>' .
                    $request->justificaçao . '</p>';
            }
            Mail::to($forum->user_email)->send(new MensagemEmail(null, 'Fórum elimindado no History4All', $mensagem));
        }
        $forum->delete();

        return response()->json(null, 201);
    }

    public function destroyComment($id, Request $request)
    {
        $comment = Comentario::findOrFail($id);

        if (!$request->user('api')) {
            if (!$request->has("email") || !$request->has("codigo")) {
                return abort(400, "necessita de provar ser o criador do comentário");
            }
            if ($request->email != $comment->user_email){
                return abort(403, "Operação negada! O utilizador não é o criador deste comentário");
            }
            if (!HistoricoGerenciadorCodigos::where('email', $request->email)->where('codigo', $request->codigo)->first()) {
                return abort(401, "Código introduzido invalido!");
            }
        } else {
            $user = User::findOrFail($request->user('api')->id);
            if ($comment->user_email != $user->email && $user->tipo != 'admin') {
                return abort(403, "não tens permissões para eliminar este comentário");
            }
        }

        //return response()->json("deleted", 201);
        $comment->delete();
        return response()->json(null, 201);
    }

    public function denuncia(Request $request, $tipo, $id){
        $request->validate(['denuncia' => 'required|string']);

        $mensagem = "";
        $link="";
        if ($tipo == "forum"){
           $forum = Forum::findOrFail($id);

           $mensagem = "Um utilizador denunciou o forum: ". $forum->titulo ." Motivo: " . $request->denuncia . ".";
           $link = "forums/" . $id;


        }else if($tipo == "comentario"){
            $comentario = Comentario::findOrFail($id);

            $forum = $comentario->forum()->first();

            $mensagem = "Um utilizador denunciou um comentario (id:".$id.") no forum: ". $forum->titulo ." Motivo: " . $request->denuncia . ".";
            $link = "forums/" . $forum->id;

        }else{
            return(abort(403, "Essa rota nao existe"));
        }

        $admin = User::where('tipo', 'admin')->first();
        $notif = new Notificacao();
        $notif->fill([
            'user_id' => $admin->id,
            'mensagem' => $mensagem,
            'remetente' => "Denuncia",
            'data' => date("Y-m-d H:i:s"),
            'lida' => "1",
            'link' => $link
        ]);
        $notif->save();

        return response()->json(null, 200);
    }

}
