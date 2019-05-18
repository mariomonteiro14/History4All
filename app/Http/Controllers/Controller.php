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

    public function notificacaoEEmail($user, $notificacaoMensagem, $assunto, $emailMensagem){
        $de = User::findOrFail(Auth::id());
        $notificacao = new Notificacao();
        $notificacao->fill([
            'user_id' => $user->id,
            'mensagem' => $notificacaoMensagem,
            'de' => $de->tipo . " " . $de->nome,
            'data' => date("Y-m-d H:i:s"),
            'nova' => '1'
        ]);
        $notificacao->save();
        Mail::to($user->email)->send(new MensagemEmail($de, $assunto, $emailMensagem));
        
        //Mail::send('emails.hello', $data, function($message) use ($data) { $message->from($data['email'] , $data['first_name']);
        //$message->to($user->email, $user->nome)->subject($notificacaoMensagem); });
    }
}
