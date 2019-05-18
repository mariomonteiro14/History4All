<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InserirPassword extends Mailable
{
    use Queueable, SerializesModels;
    public $link;
    public $blade;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($link, $blade)
    {
        $this->link = $link;
        $this->blade = $blade;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->link == 'email.registar'){
            $this->subject('Confirmação de email - History4All');
        }else{
            $this->subject('Nova password - History4All');
        }
        return $this->view($this->blade);
    }
}
