<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UserBreakAdvisor extends Notification
{
    use Queueable;

    public function __construct(
        public User $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'advisor_connection_broken',
            'title'   => 'Koneksi Diputus',
            'message' => "{$this->actor->name} telah memutuskan koneksi dengan Anda.",
            'icon'    => 'user-x',
            'url'     => route('user.clients.index'),
            'actor_id' => $this->actor->id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}