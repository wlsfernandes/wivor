<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create customer-submitted photo removal/report requests for administrator review. */
    public function up(): void
    {
        Schema::create('photo_removal_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->string('requester_name');
            $table->string('requester_email');
            $table->string('reason', 40);
            $table->text('explanation')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /** Drop photo removal requests. */
    public function down(): void
    {
        Schema::dropIfExists('photo_removal_requests');
    }
};
