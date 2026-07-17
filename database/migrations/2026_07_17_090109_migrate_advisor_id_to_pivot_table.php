<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('advisor_client_requests') || !Schema::hasTable('advisor_user')) {
            return;
        }

        $rows = DB::table('advisor_client_requests')
            ->where('status', 'approved')
            ->get(['client_id', 'advisor_id', 'created_at', 'updated_at']);

        foreach ($rows as $row) {
            DB::table('advisor_user')->insertOrIgnore([
                'user_id'    => $row->client_id,
                'advisor_id' => $row->advisor_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        // Data migration — no revert
    }
};
