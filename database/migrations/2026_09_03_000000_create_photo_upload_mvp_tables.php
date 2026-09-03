<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->timestamp('gallery_published_at')->nullable()->after('published_at');
            $table->timestamp('sales_close_at')->nullable()->after('gallery_published_at')->index();
            $table->timestamp('retention_warning_14_sent_at')->nullable()->after('sales_close_at');
            $table->timestamp('retention_warning_3_sent_at')->nullable()->after('retention_warning_14_sent_at');
        });

        Schema::table('photographers', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });

        DB::table('events')->whereNull('uuid')->orderBy('id')->eachById(
            fn ($event) => DB::table('events')->where('id', $event->id)->update(['uuid' => (string) Str::uuid()])
        );
        DB::table('photographers')->whereNull('uuid')->orderBy('id')->eachById(
            fn ($photographer) => DB::table('photographers')->where('id', $photographer->id)->update(['uuid' => (string) Str::uuid()])
        );

        Schema::table('event_photographer', function (Blueprint $table): void {
            $table->string('status', 20)->default('approved')->after('photographer_id')->index();
            $table->timestamp('upload_deadline_at')->nullable()->after('status');
            $table->timestamp('rights_confirmed_at')->nullable()->after('upload_deadline_at');
            $table->unique(['event_id', 'photographer_id']);
        });

        Schema::create('upload_batches', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('photographer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_id')->constrained('event_photographer')->cascadeOnDelete();
            $table->unsignedInteger('selected_count')->default(0);
            $table->unsignedInteger('uploaded_count')->default(0);
            $table->unsignedInteger('ready_count')->default(0);
            $table->unsignedInteger('rejected_count')->default(0);
            $table->unsignedInteger('published_count')->default(0);
            $table->string('status', 24)->default('pending')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('photos', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('photographer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_id')->constrained('event_photographer')->cascadeOnDelete();
            $table->foreignId('upload_batch_id')->constrained('upload_batches')->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('original_key', 700);
            $table->string('preview_key', 700)->nullable();
            $table->string('thumbnail_key', 700)->nullable();
            $table->string('detected_mime', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('color_mode', 20)->nullable();
            $table->char('checksum', 64)->nullable();
            $table->string('status', 24)->default('queued')->index();
            $table->string('rejection_code', 50)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('sale_count')->default(0);
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('most_recent_purchase_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deletion_reason')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'photographer_id', 'checksum'], 'photos_event_photographer_checksum_unique');
            $table->index(['event_id', 'photographer_id', 'status']);
        });

        Schema::create('media_retention_holds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('photo_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->timestamp('review_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['event_id', 'released_at']);
        });

        Schema::create('media_activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('photo_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60)->index();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_activity_logs');
        Schema::dropIfExists('media_retention_holds');
        Schema::dropIfExists('photos');
        Schema::dropIfExists('upload_batches');

        Schema::table('event_photographer', function (Blueprint $table): void {
            $table->dropUnique(['event_id', 'photographer_id']);
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'upload_deadline_at', 'rights_confirmed_at']);
        });
        Schema::table('photographers', fn (Blueprint $table) => $table->dropColumn('uuid'));
        Schema::table('events', function (Blueprint $table): void {
            $table->dropIndex(['sales_close_at']);
            $table->dropColumn(['uuid', 'ends_at', 'gallery_published_at', 'sales_close_at', 'retention_warning_14_sent_at', 'retention_warning_3_sent_at']);
        });
    }
};
