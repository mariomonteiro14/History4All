<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConfirmarNovoEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $token;
    public $email;
    public $url;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token, $email)
    {
        $this->$token = $token;
        $this->email = $email;
        $emailDividido = explode('@', $email);
        $this->url = '/users/alterarEmail/' . $token . '/' . $emailDividido[0] . '?' . $emailDividido[1];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Alteração de email - History4All');
        return $this->view('email.alterarEmail');
    }
}
