<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** Add approval, consent, review, and Stripe onboarding state. */
    public function up(): void
    {
        Schema::table('photographers', function (Blueprint $table): void {
            $table->string('status', 20)->default('pending')->index();
            $table->text('status_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('age_confirmed_at')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('stripe_account_id')->nullable()->unique();
            $table->string('stripe_onboarding_status', 30)->default('not_started');
        });

        // Preserve access for photographer records created before approval existed.
        DB::table('photographers')->update(['status' => 'approved']);
    }

    /** Remove photographer approval fields. */
    public function down(): void
    {
        Schema::table('photographers', function (Blueprint $table): void {
            $table->dropForeign(['reviewed_by']);
            $table->dropUnique(['stripe_account_id']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'status_reason',
                'reviewed_by',
                'reviewed_at',
                'age_confirmed_at',
                'terms_accepted_at',
                'stripe_account_id',
                'stripe_onboarding_status',
            ]);
        });
    }
};
