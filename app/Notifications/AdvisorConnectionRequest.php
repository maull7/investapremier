<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdvisorConnectionRequest extends Notification
{
    use Queueable;

    public function __construct(public User $advisor) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'advisor_connection_request',
            'title'   => 'Permintaan Koneksi Advisor',
            'message' => "Advisor {$this->advisor->name} ingin menghubungkan Anda sebagai klien.",
            'icon'    => 'user-plus',
            'url'     => route('user.clients.requests.index'),
            'advisor_id' => $this->advisor->id,
        ];
    }
}
