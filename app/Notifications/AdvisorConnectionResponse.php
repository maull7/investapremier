<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdvisorConnectionResponse extends Notification
{
    use Queueable;

    public function __construct(
        public User $actor,
        public string $status,
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
            'title'   => 'Respons Koneksi',
            'message' => "{$this->actor->name} telah {$label} permintaan koneksi Anda.",
            'icon'    => $this->status === 'approved' ? 'user-check' : 'user-x',
            'url'     => route('user.clients.requests.index'),
            'actor_id' => $this->actor->id,
        ];
    }
}
