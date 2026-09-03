<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Add the per-photo sale price, frozen in cents, for an event. */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->unsignedInteger('price_cents')->nullable()->after('sales_close_at');
        });
    }

    /** Remove the event sale price. */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn('price_cents');
        });
    }
};
