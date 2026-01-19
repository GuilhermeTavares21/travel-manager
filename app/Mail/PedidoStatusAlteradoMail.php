<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PedidoStatusAlteradoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;
    public $novoStatus;

    public function __construct(Pedido $pedido, $novoStatus)
    {
        $this->pedido = $pedido;
        $this->novoStatus = $novoStatus;
    }

    public function build()
    {
        return $this->subject('Atualização do seu pedido - Onfly')
                    ->view('emails.pedido_status_alterado');
    }
}
