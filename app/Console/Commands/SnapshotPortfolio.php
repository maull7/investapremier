<?php

namespace App\Console\Commands;

use App\Models\MemberPortfolio;
use App\Models\PortfolioSnapshot;
use App\Models\PortofolioItem;
use App\Models\User;
use Illuminate\Console\Command;

class SnapshotPortfolio extends Command
{
    protected $signature = 'snapshot:portfolio';
    protected $description = 'Snapshot portfolio values for all users';

    public function handle(): void
    {
        $users = User::whereIn('role', ['user', 'advisor'])->get();
        $count = 0;

        foreach ($users as $user) {
            $memberTotal = MemberPortfolio::where('user_id', $user->id)->sum('total_nilai');
            $itemTotal = PortofolioItem::where('user_id', $user->id)->sum('nilai');
            $totalValue = $memberTotal + $itemTotal;

            $cashValue = PortofolioItem::where('user_id', $user->id)
                ->whereIn('jenis', ['Kas/Deposito', 'Kas', 'Deposito'])
                ->sum('nilai');
            $cashValue += MemberPortfolio::where('user_id', $user->id)
                ->whereIn('jenis', ['Kas/Deposito', 'Kas', 'Deposito'])
                ->sum('total_nilai');

            PortfolioSnapshot::create([
                'user_id' => $user->id,
                'total_value' => $totalValue,
                'asset_value' => $totalValue - $cashValue,
                'cash_value' => $cashValue,
                'recorded_at' => now(),
            ]);

            $count++;
        }

        $this->info("Snapshots recorded for {$count} users.");
    }
}
