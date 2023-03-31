<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Channels\WhatsAppChannel;
use App\Models\Comanda;

class CerereStatus extends Notification
{
    use Queueable;


  public $comanda;

  public function __construct(Comanda $comanda)
  {
    $this->comanda = $comanda;
  }

  public function via($notifiable)
  {
    return [WhatsAppChannel::class];
  }

  public function toWhatsApp($notifiable)
  {
    // $orderUrl = url("/orders/{$this->order->id}");
    // $company = 'Acme';
    // $deliveryDate = $this->order->created_at->addDays(4)->toFormattedDateString();

    $orderUrl = 'urlllll';
    $company = 'Companyyyy';
    $deliveryDate = $this->comanda->created_at->addDays(4)->toFormattedDateString();

    return (new WhatsAppMessage)
        // ->content("Your {$company} order of {$this->comanda->transportator_contract} has shipped and should be delivered on {$deliveryDate}. Details: {$orderUrl}");
        ->content('Va rugam accesati ' . url('/cerere-status-comanda/whatsapp/' . $this->comanda->cheie_unica) . ' , pentru a ne transmite statusul comenzii. Multumim, Maseco Expres!');
  }
}
