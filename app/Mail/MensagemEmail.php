<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MensagemEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $de;
    public $assunto;
    public $mensagem;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($de, $assunto, $mensagem)
    {
        $this->de = $de;
        $this->assunto = $assunto;
        $this->mensagem = $mensagem;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->de){
            $this->from($this->de->email, $this->de->nome);
        }
        $this->subject($this->assunto);
        return $this->view('email.vazio');
    }
}
