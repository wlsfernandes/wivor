<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Add optional per-event commission and packet-discount percentages; null means use the global default. */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->decimal('commission_percentage', 5, 2)->nullable()->after('price_cents');
            $table->decimal('packet_discount_percentage', 5, 2)->nullable()->after('commission_percentage');
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE events ADD CONSTRAINT events_commission_percentage_range CHECK (commission_percentage BETWEEN 0 AND 100)');
        DB::statement('ALTER TABLE events ADD CONSTRAINT events_packet_discount_percentage_range CHECK (packet_discount_percentage BETWEEN 0 AND 100)');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE events DROP CONSTRAINT IF EXISTS events_commission_percentage_range');
            DB::statement('ALTER TABLE events DROP CONSTRAINT IF EXISTS events_packet_discount_percentage_range');
        }

        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn(['commission_percentage', 'packet_discount_percentage']);
        });
    }
};
