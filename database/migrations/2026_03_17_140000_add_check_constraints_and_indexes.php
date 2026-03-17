<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            // Prevent negative balance
            DB::statement('ALTER TABLE accounts ADD CONSTRAINT check_balance_non_negative CHECK (balance >= 0)');

            // Prevent negative values in CDRs
            DB::statement('ALTER TABLE cdrs ADD CONSTRAINT check_duration_non_negative CHECK (duration >= 0)');
            DB::statement('ALTER TABLE cdrs ADD CONSTRAINT check_billsec_non_negative CHECK (billsec >= 0)');
            DB::statement('ALTER TABLE cdrs ADD CONSTRAINT check_cost_non_negative CHECK (cost >= 0)');

            // Prevent negative values in tariffs
            DB::statement('ALTER TABLE tariffs ADD CONSTRAINT check_price_non_negative CHECK (price_per_minute >= 0)');
            DB::statement('ALTER TABLE tariffs ADD CONSTRAINT check_fee_non_negative CHECK (connection_fee >= 0)');
            DB::statement('ALTER TABLE tariffs ADD CONSTRAINT check_free_seconds_non_negative CHECK (free_seconds >= 0)');

            // Composite index for active calls query (Account::activeCalls)
            DB::statement('CREATE INDEX idx_cdrs_account_status ON cdrs (account_id, status)');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE accounts DROP CONSTRAINT IF EXISTS check_balance_non_negative');
            DB::statement('ALTER TABLE cdrs DROP CONSTRAINT IF EXISTS check_duration_non_negative');
            DB::statement('ALTER TABLE cdrs DROP CONSTRAINT IF EXISTS check_billsec_non_negative');
            DB::statement('ALTER TABLE cdrs DROP CONSTRAINT IF EXISTS check_cost_non_negative');
            DB::statement('ALTER TABLE tariffs DROP CONSTRAINT IF EXISTS check_price_non_negative');
            DB::statement('ALTER TABLE tariffs DROP CONSTRAINT IF EXISTS check_fee_non_negative');
            DB::statement('ALTER TABLE tariffs DROP CONSTRAINT IF EXISTS check_free_seconds_non_negative');
            DB::statement('DROP INDEX IF EXISTS idx_cdrs_account_status');
        }
    }
};
