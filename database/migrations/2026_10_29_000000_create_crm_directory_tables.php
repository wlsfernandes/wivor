<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create the CRM contact directory, event participant links, imports, consents and suppressions. */
    public function up(): void
    {
        Schema::create('crm_contacts', function (Blueprint $table): void {
            $table->id();
            $table->string('email_normalized', 320)->unique();
            $table->string('email_original', 320);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('phone', 40)->nullable();
            $table->timestampsTz();
        });

        Schema::create('crm_imports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('original_filename');
            $table->string('disk_path');
            $table->char('file_sha256', 64);
            $table->json('mapping');
            $table->string('data_source');
            $table->string('organizer_name')->nullable();
            $table->string('consent_status', 20);
            $table->text('consent_evidence')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->string('error_report_path')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('crm_event_contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crm_contact_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('crm_import_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('bib_number', 30)->nullable();
            $table->string('division', 100)->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestampsTz();

            // Also serves as the index for event_id lookups.
            $table->unique(['event_id', 'crm_contact_id']);
        });

        Schema::create('crm_contact_consents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('crm_contact_id')->constrained()->cascadeOnDelete();
            $table->string('category', 20);
            // Null scope means WiVor-wide; an event scope disappears with its event instead of widening.
            $table->foreignId('scope_event_id')->nullable()->index()->constrained('events')->cascadeOnDelete();
            $table->string('status', 20);
            $table->string('source');
            $table->text('evidence')->nullable();
            $table->string('policy_version', 50)->nullable();
            $table->timestampTz('recorded_at');
            $table->timestampTz('revoked_at')->nullable();
            $table->timestampsTz();

            // Eligibility checks filter consents by contact and category.
            $table->index(['crm_contact_id', 'category']);
        });

        Schema::create('crm_suppressions', function (Blueprint $table): void {
            $table->id();
            // Suppressions outlive contact deletion so an erased address stays blocked.
            $table->foreignId('crm_contact_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('email_normalized', 320)->index();
            $table->string('reason', 20);
            $table->string('category', 20);
            $table->foreignId('scope_event_id')->nullable()->index()->constrained('events')->cascadeOnDelete();
            $table->timestampsTz();
        });
    }

    /** Drop the CRM directory tables in dependency order. */
    public function down(): void
    {
        Schema::dropIfExists('crm_suppressions');
        Schema::dropIfExists('crm_contact_consents');
        Schema::dropIfExists('crm_event_contacts');
        Schema::dropIfExists('crm_imports');
        Schema::dropIfExists('crm_contacts');
    }
};
