<?php

namespace App\Http\Controllers;

use App\Escola;
use App\Mail\ContactarAdmin;
use App\Mail\InserirPassword;
use App\Mail\MensagemEmail;
use App\Mail\ConfirmarNovoEmail;
use App\Notificacao;
use App\Turma;
use Illuminate\Http\Request;

use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

use App\User;
use DateTime;

define('YOUR_SERVER_URL', 'http://h4a.local/');
// Check "oauth_clients" table for next 2 values:
define('CLIENT_ID', '2');
define('CLIENT_SECRET', '9lAzFCSTCUbsnn8WlWYJozLOIdT2givB9TmF03FJ');

class UserControllerAPI extends Controller
{
    ///APAGAR
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
        return new UserResource($user);
    }

    public function editProfile(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        if ($request->has('newEmail') && $request->input('newEmail') != "" && $user->email != $request->newEmail) {
            Mail::to($user->email)->send(new ConfirmarNovoEmail(Auth::id(), $request->newEmail));
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
            $filename = str_random(16) . '.' . $request->newFoto->getClientOriginalExtension();;
            Storage::disk('public')->putFileAs('profiles', $request->newFoto, $filename);
            $user->foto = $filename;
        }

        $user->save();

        return response()->json([
            'data' => $user
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
                abort(403, 'Unknown user');
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
            return response()->json(['message' => 'User credentials are invalid'], $errorCode);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'tipo' => 'required|in:admin,professor,aluno',
            'escola' => 'nullable',
            'turma' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error while registering user'
            ], 400);
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

        $user->setRememberToken(Str::random(10));


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
                $filename = str_random(16) . '.' . $request->foto->getClientOriginalExtension();;
                Storage::disk('public')->putFileAs('profiles', $request->foto, $filename);

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
                if (!$escola){
                    $this->notificacaoEEmail($user, 
                        "Foi removido(a) da escola " . $escola->nome . " estando de momento sem escola associada", 
                        "Ficou sem escola associada escola associada",
                        "<h3>Foi removido(a) da sua escola no <a href='http://142.93.219.146/'>History4All</a></h3><p>Já não está na escola " . $escola->nome . ". De momento não tem escola associada</p>");
                } else{
                    $this->notificacaoEEmail($user, 
                        "Foi movido(a) para a escola " . $escola->nome . " estando de momento sem escola associada", 
                        "A sua escola foi alterada",
                        "<h3>Foi movido(a) para a escola no <a href='http://142.93.219.146/'>History4All</a></h3><p>A sua escola passou a ser a " . $escola->nome . "</p>");
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
        if (Auth::user()->tipo == "professor" && $user->turma()->first()->professor_id != Auth::id()){
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }
        if ($user->trashed()) {
            $this->notificacaoEEmail($user, "A sua conta foi apagada", "A sua conta foi apagada",
                "<h3>A sua conta no <a href='<ahttp://142.93.219.146/'>History4All</a> foi permanentemente apagada</h3>");
            Storage::disk('public')->delete('profiles/' . $user->foto);
            $user->forceDelete();
        } else {
            $this->notificacaoEEmail($user, "A sua conta foi apagada", "A sua conta foi apagada",
                "<h3>A sua conta no <a href='http://142.93.219.146/'>History4All</a> foi apagada</h3>");
            $user->delete();
        }
        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->find($id);
        $user->restore();
        $this->notificacaoEEmail($user, "A sua conta foi restaurada", "A sua conta foi restaurada",
            "<h3>A sua conta no <a href='http://142.93.219.146/'>History4All</a> foi restaurada</h3>");
        return response()->json("user restored", 201);
    }

    public function contactHistory4all(Request $request){
        Validator::make($request->all(), [
            'assunto' => 'required|string|min:6',
            'email' => 'required|string|email',
            'texto' => 'required|string|min:50',
        ]);
        $para = User::where('tipo','admin')->first();
        $de = new UserResource(User::where('email', $request->email)->first()) ?? $request->email;
        Mail::to($para->email)->send(new ContactarAdmin($de , $request->assunto, $request->texto));
    }

    public function notificacoes(Request $request){
        return response()->json([
            'data' => Notificacao::where('user_id', Auth::id())->select('id', 'mensagem', 'nova', 'de', 'data')->orderBy('id', 'desc')->get()
        ]);
    }

    public function storeNotificacao(Request $request) {
        $request->validate([
            'users' => 'required',
            'mensagem' => 'required|min:1',
            'de' => 'required'
        ]);

        if (!$request->has('users') || sizeof($request->get('users')) < 1) {
            abort(403, 'Unauthorized action.');
        }

        $userIds = array_column($request->get('users'), 'id');
        foreach ($userIds as $userId) {
            $notificacao = new Notificacao();
            $notificacao->fill([
                'user_id' => $userId,
                'mensagem' => $request->mensagem,
                'de' => $request->de,
                'data' => date("Y-m-d H:i:s"),
                'nova' => '1'
            ]);
            $notificacao->save();
        };
        return response()->json(null, 201);
    }

    public function updateNotificacoes(Request $request){
        $notificacoes = Notificacao::where('user_id', Auth::id())->where('nova', 1)->get();
        foreach ($notificacoes as $notificacao) {
            $notificacao->nova = 0;
            $notificacao->save();
        };

        return response()->json(Notificacao::where('user_id', Auth::id())->get(), 201);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Email invalido ou nao registado'
            ], 400);
        }
        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at == '') abort(403, 'Conta não verificada');

        $user->setRememberToken(Str::random(10));
        $user->save();
        Mail::to($user->email)->send(new InserirPassword('/users/resetPassword/' . $user->getRememberToken(), 'email.resetPassword'));
        return response()->json([
            'message' => 'Email enviado'
        ], 200);
    }

    public function novaPassword(Request $request, $id){
        $request->validate([
            'password' => 'required|min:4',
        ]);

        $user = User::findOrFail($id);
        if ($user && $request->has('password')) {
            $user->password = bcrypt($request->input('password'));
            $user->remember_token = '';
            $user->save();
            return response()->json([
                'data' => $user,
                'message' => 'Password alterada'
            ], 201);
        }
    }

    public function novoEmail(Request $request, $id){
        if (Auth::id() != $id) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'email' => 'required|string|email|unique:users',
        ]);
        $user = User::findOrFail($id);
        $user->email = $request->email;
        $user->save();
        return response()->json([
            'data' => $user,
            'message' => 'Email alterado'
        ], 201);
    }
}
