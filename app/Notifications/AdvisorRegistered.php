<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdvisorRegistered extends Notification
{
    use Queueable;

    public function __construct(
        public User $advisor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'      => 'advisor_registered',
            'advisor_id'=> $this->advisor->id,
            'name'      => $this->advisor->name,
            'email'     => $this->advisor->email,
            'title'     => 'Pendaftaran Advisor Baru',
            'message'   => "{$this->advisor->name} ({$this->advisor->email}) mendaftar sebagai Advisor dan menunggu persetujuan.",
            'url'       => route('admin.advisors.index'),
        ];
    }
}
