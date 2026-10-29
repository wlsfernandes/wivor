<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Add mapping presets, import consent scope, failure and purge tracking, and contact origin. */
    public function up(): void
    {
        Schema::create('crm_mapping_presets', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->json('mapping');
            $table->timestampsTz();
        });

        Schema::table('crm_imports', function (Blueprint $table): void {
            $table->string('consent_category', 20)->nullable()->after('consent_status');
            $table->string('consent_scope', 20)->nullable()->after('consent_category');
            $table->timestampTz('files_purged_at')->nullable()->after('finished_at');
            $table->string('failure_message')->nullable()->after('status');
        });

        Schema::table('crm_contacts', function (Blueprint $table): void {
            $table->string('first_source', 30)->nullable()->after('phone');
        });
    }

    /** Remove the Phase 2 CRM import columns and presets. */
    public function down(): void
    {
        Schema::table('crm_contacts', function (Blueprint $table): void {
            $table->dropColumn('first_source');
        });

        Schema::table('crm_imports', function (Blueprint $table): void {
            $table->dropColumn(['consent_category', 'consent_scope', 'files_purged_at', 'failure_message']);
        });

        Schema::dropIfExists('crm_mapping_presets');
    }
};
