<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class NovaSenhaUsuarioEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $senha;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $senha)
    {
        $this->user = $user;
        $this->senha = $senha;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Nova senha para o sistema de eleições AFISVEC')
            ->view('emails.novasenhausuario')
            ->with(['user' => $this->user, 'senha' => $this->senha]);
    }
}
