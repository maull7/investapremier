<?php

namespace App\Notifications;

use App\Models\StockPriceAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockPriceAlertTriggered extends Notification
{
    use Queueable;

    public function __construct(
        public StockPriceAlert $alert,
        public float $currentPrice,
        public ?string $tanggalHarga = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $direction = $this->alert->condition === 'above' ? 'naik' : 'turun';
        $compare   = $this->alert->condition === 'above' ? 'mencapai/di atas' : 'mencapai/di bawah';

        return [
            'type'           => 'stock_price_alert',
            'alert_id'       => $this->alert->id,
            'stock_id'       => $this->alert->stock_id,
            'kode_efek'      => $this->alert->kode_efek,
            'condition'      => $this->alert->condition,
            'target_price'   => (float) $this->alert->target_price,
            'current_price'  => $this->currentPrice,
            'tanggal_harga'  => $this->tanggalHarga,
            'note'           => $this->alert->note,
            'title'          => "Alert Harga {$this->alert->kode_efek}",
            'message'        => "Saham {$this->alert->kode_efek} {$direction} ke "
                                . number_format($this->currentPrice, 2, ',', '.')
                                . " ({$compare} target "
                                . number_format((float) $this->alert->target_price, 2, ',', '.') . ')',
            'icon'           => $this->alert->condition === 'above' ? 'trending-up' : 'trending-down',
            'url'            => route('user.saham.show', ['stock' => $this->alert->stock_id ?? $this->alert->kode_efek], false),
        ];
    }
}
