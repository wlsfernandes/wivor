<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create email templates, campaigns, their selected events and recipient snapshots. */
    public function up(): void
    {
        Schema::create('crm_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('subject');
            $table->string('preview_text')->nullable();
            $table->text('body_html');
            $table->text('text_body')->nullable();
            $table->string('cta_label', 60)->nullable();
            $table->string('cta_url', 2048)->nullable();
            $table->timestampsTz();
        });

        Schema::create('crm_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('category', 30);
            $table->string('audience_type', 20);
            $table->string('status', 20)->default('draft');
            $table->foreignId('crm_template_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->string('preview_text')->nullable();
            $table->text('body_html');
            $table->text('text_body')->nullable();
            $table->string('cta_label', 60)->nullable();
            $table->string('cta_url', 2048)->nullable();
            $table->timestampTz('scheduled_at')->nullable();
            $table->unsignedInteger('intended_count')->default(0);
            $table->unsignedInteger('ineligible_count')->default(0);
            $table->timestampsTz();

            // The dispatcher looks for scheduled campaigns that are due.
            $table->index(['status', 'scheduled_at']);
        });

        Schema::create('crm_campaign_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('crm_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->index()->constrained()->restrictOnDelete();
            $table->timestampsTz();

            $table->unique(['crm_campaign_id', 'event_id']);
        });

        Schema::create('crm_campaign_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('crm_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crm_contact_id')->index()->constrained()->cascadeOnDelete();
            // Event context for personalization; null when a campaign targets several events or the directory.
            $table->foreignId('event_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->timestampsTz();

            // One copy per address per campaign; also serves campaign_id lookups.
            $table->unique(['crm_campaign_id', 'crm_contact_id']);
            $table->index(['crm_campaign_id', 'status']);
        });
    }

    /** Drop the campaign tables in dependency order. */
    public function down(): void
    {
        Schema::dropIfExists('crm_campaign_recipients');
        Schema::dropIfExists('crm_campaign_events');
        Schema::dropIfExists('crm_campaigns');
        Schema::dropIfExists('crm_templates');
    }
};
