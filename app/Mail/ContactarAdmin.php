<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactarAdmin extends Mailable
{
    use Queueable, SerializesModels;
    public $de;
    public $assunto;
    public $texto;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($de, $assunto, $texto)
    {
        $this->de = $de;
        $this->assunto = $assunto;
        $this->texto = $texto;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (is_object($this->de) && $this->de->email){
            $nome = $this->de->tipo . ' ' . $this->de->nome;
            if ($this->de->tipo == 'aluno'){
                $nome .= ' - ' . $this->de->escola->nome . ' - ' . $this->de->turma->nome;
            }
            $nome .= ' - ' . $this->de->email;
            $this->from($this->de->email, $nome);
        }else{
            $this->from($this->de, $this->de);
        }
        $this->subject($this->assunto);
        return $this->view('email.contactarAdmin');
    }
}
