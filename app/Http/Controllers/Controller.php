<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\Notificacao;
use App\Mail\MensagemEmail;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function notificacaoEEmail($user, $notificacaoMensagem, $emailMensagem){
        //notificação
        $de = User::findOrFail(Auth::id());
        $request= [
            'userId' => $user->id,
            'mensagem' => $notificacaoMensagem,
            'de' => $de->tipo . " " . $de->nome,
            ];
        $this->enviarNotificacao($request);
        //email
        Mail::to($user->email)->send(new MensagemEmail($emailMensagem));
    }

    public function enviarNotificacao($request) {
        $notificacao = new Notificacao();
        $notificacao->fill([
            'user_id' => $request['userId'],
            'mensagem' => $request['mensagem'],
            'de' => $request['de'],
            'data' => date("Y-m-d H:i:s"),
            'nova' => '1'
        ]);
        $notificacao->save();
    }
}
