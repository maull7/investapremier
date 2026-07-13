<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdvisorConnectionResponse extends Notification
{
    use Queueable;

    public function __construct(
        public User $client,
        public string $status, // approved / rejected
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $label = $this->status === 'approved' ? 'menyetujui' : 'menolak';

        return [
            'type'    => 'advisor_connection_response',
            'title'   => 'Respons Koneksi Klien',
            'message' => "{$this->client->name} telah {$label} permintaan koneksi Anda.",
            'icon'    => $this->status === 'approved' ? 'user-check' : 'user-x',
            'url'     => route('user.clients.index'),
            'client_id' => $this->client->id,
        ];
    }
}
