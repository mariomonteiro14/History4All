<?php

namespace App\Http\Controllers;

use App\Escola;
use App\Mail\ContactarAdmin;
use App\Mail\InserirPassword;
use App\Mail\MensagemEmail;
use App\Mail\ConfirmarNovoEmail;
use App\Notificacao;
use App\Turma;
use App\Chat;
use Illuminate\Http\Request;

use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

use App\User;
use DateTime;

define('YOUR_SERVER_URL', 'http://h4a.local/');
//define('YOUR_SERVER_URL', 'http://history4all.test/');
// Check "oauth_clients" table for next 2 values:
define('CLIENT_ID', '2');
define('CLIENT_SECRET', '9lAzFCSTCUbsnn8WlWYJozLOIdT2givB9TmF03FJ');

class UserControllerAPI extends Controller
{

    public function users(Request $request)
    {
        $users = UserResource::collection(User::all());
        return response()->json([
            'data' => $users,
        ]);
    }

    public function usersTrashed(Request $request)
    {
        $users = UserResource::collection(User::onlyTrashed()->get());
        return response()->json([
            'data' => $users,
        ]);
    }

    ///AGAGAR
    public function alunos(Request $request)
    {
        $users = UserResource::collection(User::where('tipo', 'aluno')->orderBy('nome')->get());
        return response()->json([
            'data' => $users,
        ]);
    }

    ///APAGAR
    public function professores(Request $request)
    {
        $users = UserResource::collection(User::where('tipo', 'professor')->get());
        return response()->json([
            'data' => $users,
        ]);
    }


    public function myProfile(Request $request)
    {
        return new UserResource($request->user());
    }

    public function getUser($id)
    {
        $user = User::findOrFail($id);
        $userAtual = User::where('id', Auth::id())->first();
        if (($userAtual->tipo == 'aluno' && $userAtual->escola_id != $user->escola_id) ||
            ($userAtual->tipo == 'professor' && $user->tipo == 'aluno' && $userAtual->escola_id != $user->escola_id) ||
            ($userAtual->tipo == "admin" && $user->tipo == "aluno") || ($userAtual->tipo != "admin" && $user->tipo == "admin")) {
            return null;
        }
        return new UserResource($user);
    }

    public function editProfile(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        if ($request->has('newEmail') && $request->input('newEmail') != "" && $user->email != $request->newEmail) {
            if (User::where('email', $request->newEmail)->first()) {
                return abort(403, "Email invalido: Ja registado.");
            }
            $user->setRememberToken(Str::random(100));
            Mail::to($request->newEmail)->send(new ConfirmarNovoEmail($user->getRememberToken(), $request->newEmail));
            //$user->email = $request->newEmail;
        }

        if ($request->has('newPassword') && $request->input('newPassword') != "") {
            $user->password = bcrypt($request->newPassword);
        }

        if ($request->has('newFoto')) {
            //Delete old photo
            if ($user->foto && $user->foto != "") {
                Storage::disk('public')->delete('profiles/' . $user->foto);
            }

            //Store new photo
            $filename = str_random(16) . '.' . $request->newFoto->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('profiles', $request->newFoto, $filename);
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                exec('exiftool -all= /var/www/html/History4All/public/storage/profiles/' . $filename);
                Storage::disk('public')->delete('profiles/' . $filename . '_original');
            }

            $user->foto = $filename;
        }

        $user->save();

        return response()->json([
            'data' => new UserResource($user)
        ], 201);

    }

    public function login(Request $request)
    {
        $http = new \GuzzleHttp\Client;

        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $request->email)->first();
            if ($user != null) {
                if ($user->email_verified_at == '') abort(403, 'Conta não verificada');
            } else {
                abort(403, 'Não existe nenhum utilizador com esse email');
            }
        }
        $response = $http->post(YOUR_SERVER_URL . '/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => CLIENT_ID,
                'client_secret' => CLIENT_SECRET,
                'username' => $request->email,
                'password' => $request->password,
                'scope' => ''
            ],
            'exceptions' => false,
        ]);

        $errorCode = $response->getStatusCode();
        if ($errorCode == '200') {
            return json_decode((string)$response->getBody(), true);
        } else {
            return response()->json(['message' => 'As credenciais do utilizador estão erradas'], $errorCode);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'tipo' => 'required|in:admin,professor,aluno',
            'escola' => 'nullable',
            'turma' => 'nullable',
        ]);

        if ((Auth::user()->tipo == "admin" && $request->tipo == "aluno") ||
                Auth::user()->tipo == "professor" && $request->tipo != "aluno"){
            return abort(403, "Nao tem permissoes para criar ". $request->tipo);
        }
        $user = new User([
            'nome' => $request->input('nome'),
            'email' => $request->input('email'),
            'tipo' => $request->input('tipo'),
            'password' => 'secret',
        ]);
        if ($user->tipo == "admin") {
            $user->escola_id = null;
            $user->turma_id = null;
        } else {
            $escola = Escola::where('nome', $request->escola)->first();
            $user->escola_id = $escola->id;

            if ($user->tipo == "aluno" && $request->has('turma') && $request->turma != "") {
                $turma = Turma::where('escola_id', $escola->id)->where('nome', $request->turma)->first();
                $user->turma_id = $turma->id;
            } else {
                $user->turma_id = null;
            }
        }

        $user->setRememberToken(Str::random(100));


        //enviar email
        Mail::to($user->email)->send(new InserirPassword('/users/registarPassword/' . $user->getRememberToken(), 'email.registar'));
        $user->save();
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    public function getUserByToken(Request $request, $token)
    {
        return User::where('remember_token', $token)->first();
    }

    public function logout()
    {
        Auth::guard('api')->user()->token()->revoke();
        Auth::guard('api')->user()->token()->delete();
        return response()->json(['msg' => 'Token revoked'], 200);
    }

    public function activateAccount(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:4',
            'foto' => 'nullable|file|mimes:jpeg,bmp,png,jpg'
        ]);

        $user = User::findOrFail($id);
        if ($user && $request->has('password')) {
            $user->password = bcrypt($request->input('password'));
            $user->email_verified_at = date("Y-m-d H:i:s");
            $user->remember_token = '';

            if ($request->has('foto')) {
                $filename = str_random(16) . '.' . $request->foto->getClientOriginalExtension();
                Storage::disk('public')->putFileAs('profiles', $request->foto, $filename);
                if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                    exec('exiftool -all= /var/www/html/History4All/public/storage/profiles/' . $filename);
                    Storage::disk('public')->delete('profiles/' . $filename . '_original');
                }
                $user->foto = $filename;

                /*return response()->json([
                    'data' => $user,
                ], 500);*/
            }

            $user->save();

            return response()->json([
                'data' => $user,
                'message' => 'Account confirmed'
            ], 201);
        }

    }

    public function update($id, Request $request)
    {
        if (Auth::user()->tipo != "admin") {
            abort(403, 'Unauthorized action.');
        }
        $user = User::findOrFail($id);

        if ($request->has('escola') && $user->tipo != "admin") {

            $escola = Escola::where('nome', $request->escola)->first();

            if ($escola->id != $user->escola_id) {
                $link = $user == 'aluno' ? "alunos/escola" : "professores/gestor";
                if (!$user->escola_id) {//inserido
                    $notificacaoMensagem = "Foi inserido(a) na escola " . $escola->nome;
                    $assunto = "Foi associado(a) à escola " . $escola->nome;
                    $emailMensagem = "<h3>Foi inserido(a) da sua escola no <a href='http://142.93.219.146/'>History4All</a>" .
                        "</h3><p>Agora está na escola " . $escola->nome . ".</p>";
                    $this->notificacaoEEmail($user, $notificacaoMensagem, $assunto, $emailMensagem, $link);
                } else {
                    $notificacaoMensagem = "Foi movido(a) para a escola " . $escola->nome;
                    $assunto = "Foi movido(a) para outra escola";
                    $emailMensagem = "<h3>Foi movido(a) para a escola no <a href='http://142.93.219.146/'>History4All</a>" . "
                        </h3><p>A sua escola passou a ser a " . $escola->nome . "</p>";
                    $this->notificacaoEEmail($user, $notificacaoMensagem, $assunto, $emailMensagem, $link);
                }
                $turmas = Turma::where('professor_id', $user->id)->get();
                foreach ($turmas as $turma) {
                    $turma->professor_id = null;
                    $turma->save();
                }
                $user->escola_id = $escola->id;
            }

            if ($request->has('turma') && $user->tipo == "aluno") {
                $turma = Turma::where('escola_id', $escola->id)->where('nome', $request->turma)->first();
                $user->turma_id = $turma->id;
            } else {
                $user->turma_id = null;
            }
        }

        $user->save();

        return response()->json([
            'message' => 'Successfully updated user!'
        ], 201);

    }

    public function destroy($id)
    {

        $user = User::withTrashed()->find($id);
        if (Auth::user()->tipo == "professor" && $user->turma()->first()->professor_id != Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }
        if ($user->trashed()) {
            Storage::disk('public')->delete('profiles/' . $user->foto);
            $user->forceDelete();
            $assunto = "A sua conta foi apagada";
            $emailMensagem = "<h3>A sua conta no <a href='http://142.93.219.146/'>History4All</a> foi apagada permantentemente</h3>";
            Mail::to($user->email)->send(new MensagemEmail(User::findOrFail(Auth::id()), $assunto, $emailMensagem));
        } else {
            $user->delete();
            $assunto = "A sua conta foi apagada";
            $emailMensagem = "<h3>A sua conta no <a href='http://142.93.219.146/'>History4All</a> foi apagada temporátiamente, " . "
                se deseja recurerá-la por favor contacte um dos administradores do site, para isso pode fazê-lo através da página inicial do History4All</h3>";
            Mail::to($user->email)->send(new MensagemEmail(User::findOrFail(Auth::id()), $assunto, $emailMensagem));
        }
        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->find($id);
        $user->restore();
        $assunto = "A sua conta foi restaurada";
        $emailMensagem = "<h3>A sua conta no <a href='http://142.93.219.146/'>History4All</a> foi restaurada</h3>";
        Mail::to($user->email)->send(new MensagemEmail(User::findOrFail(Auth::id()), $assunto, $emailMensagem));
        return response()->json("user restored", 201);
    }

    public function contactHistory4all(Request $request)
    {
        $request->validate([
            'assunto' => 'required|string|min:6',
            'email' => 'required|string|email',
            'texto' => 'required|string|min:10',
            'emailPara' => 'nullable|string|email',
        ]);

        $para = $request->emailPara != null ? User::where('email',$request->emailPara)->first() : User::where('tipo', 'admin')->first();
        $user = User::where('email', $request->email)->first();
        $de = $user ? new UserResource($user) : $request->email;
        Mail::to($para->email)->send(new ContactarAdmin($de, $request->assunto, $request->texto));

        $notificacao = new Notificacao();
        $mensagem = $user ? "Um ". $user->tipo ." enviou-lhe uma nova mensagem. Consulte o seu email." : "Recebeu uma nova mensagem de um utilizador não registado. Consulte o seu email.";
        $notificacao->fill([
            'user_id' => $para->id,
            'mensagem' => $mensagem,
            'de' => "",
            'data' => date("Y-m-d H:i:s"),
            'nova' => '1',
            'link' => null
        ]);
        $notificacao->save();
    }

    public function notificacoes(Request $request)
    {
        return response()->json([
            'data' => Notificacao::where('user_id', Auth::id())->select('id', 'mensagem', 'nova', 'de', 'data', 'link')->orderBy('data', 'desc')->get()
        ]);
    }

    public function storeNotificacao(Request $request)
    {
        $request->validate([
            'users' => 'required',
            'mensagem' => 'required|min:1',
            'de' => 'required'
        ]);

        if (!$request->has('users') || sizeof($request->get('users')) < 1) {
            abort(403, 'Não tem as permissoes necessárias');
        }

        $userIds = array_column($request->get('users'), 'id');

        if (count($userIds) != count($request->get('users'))){
            abort(400, 'Inseridos utilizadores inválidos');
        }

        foreach ($userIds as $userId) {
            $notificacao = new Notificacao();
            $notificacao->fill([
                'user_id' => $userId,
                'mensagem' => $request->mensagem,
                'de' => $request->de,
                'data' => date("Y-m-d H:i:s"),
                'nova' => '1',
                'link' => $request->link
            ]);
            $notificacao->save();
        };
        return response()->json(['data' => $notificacao], 201);
    }

    public function updateNotificacoes(Request $request)
    {
        $notificacoes = Notificacao::where('user_id', Auth::id())->where('nova', 1)->get();
        foreach ($notificacoes as $notificacao) {
            $notificacao->nova = 0;
            $notificacao->save();
        };

        return response()->json([
            'data' => Notificacao::where('user_id', Auth::id())->select('id', 'mensagem', 'nova', 'de', 'data', 'link')->orderBy('data', 'desc')->get()
        ], 201);
    }

    //colocar notificaçao como nao lida
    public function updateNotificacaoNaoLida($id, Request $request)
    {
        $notificacao = Notificacao::findOrFail($id);
        if ($notificacao->user_id != Auth::id()){
            abort(403, 'A notificação que está a tentar alterar não é a sua');
        }
        $notificacao->nova = 1;
        $notificacao->save();
        return response()->json(null, 200);
    }
    //colocar notificaçao como lida
    public function updateNotificacaoLida($id, Request $request)
    {
        $notificacao = Notificacao::findOrFail($id);
        if ($notificacao->user_id != Auth::id()){
            abort(403, 'A notificação que está a tentar alterar não é a sua');
        }
        $notificacao->nova = 0;
        $notificacao->save();
        return response()->json(null, 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);
        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at == '') abort(403, 'Conta não verificada');

        $user->setRememberToken(Str::random(100));
        $user->save();
        Mail::to($user->email)->send(new InserirPassword('/users/resetPassword/' . $user->getRememberToken(), 'email.resetPassword'));
        return response()->json([
            'message' => 'Email enviado'
        ], 200);
    }

    public function novaPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:4',
        ]);

        $user = User::findOrFail($id);
        if ($user && $request->has('password')) {
            $user->password = bcrypt($request->input('password'));
            $user->remember_token = '';
            $user->save();
            return response()->json([
                'data' => new UserResource($user),
                'message' => 'Password alterada'
            ], 201);
        }
    }

    public function novoEmail(Request $request, $id)
    {
        /*if (Auth::id() != $id) {
            abort(403, 'Unauthorized action.');
        }*/

        $request->validate([
            'email' => 'required|string|email|unique:users',
        ]);
        $user = User::findOrFail($id);
        $user->email = $request->email;
        $user->email_verified_at = date("Y-m-d H:i:s");
        $user->setRememberToken(null);
        $user->save();
        return response()->json([
            'data' => new UserResource($user),
            'message' => 'Email alterado'
        ], 201);
    }

    public function chatProfessores(Request $request)
    {
        if (Auth::user()->tipo != "professor") {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }
        $escola = Auth::user()->escola()->first();
        $chat = $escola->chatProfessores()->with('chatMensagens')->first();

        return response()->json([
            'data' => $chat
        ], 200);
    }
}
