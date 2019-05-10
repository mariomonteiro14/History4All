<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactarAdmin extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $assunto;
    public $texto;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $assunto, $texto)
    {
        $this->email = $email;
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
        return $this->view('email.contactarAdmin');
    }
}
