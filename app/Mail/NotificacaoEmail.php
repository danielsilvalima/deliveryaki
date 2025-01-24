<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacaoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $dados; // Dados que serão enviados ao e-mail

    public function __construct($dados)
    {
        $this->dados = $dados;
    }

    public function build()
    {
      return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
        ->subject('NOVO CLIENTE!')
        ->view('email.notificacao')
        ->with(['dados' => $this->dados]);
    }
}
