<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdvisorConnectionRequest extends Notification
{
    use Queueable;

    public function __construct(public User $client) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'      => 'advisor_connection_request',
            'title'     => 'Permintaan Koneksi dari Klien',
            'message'   => "{$this->client->name} ingin terhubung dengan Anda sebagai klien.",
            'icon'      => 'user-plus',
            'url'       => route('user.clients.index', ['tab' => 'tertunda']),
            'client_id' => $this->client->id,
        ];
    }
}
