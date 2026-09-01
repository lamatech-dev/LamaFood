<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('type', 16);
            $table->string('label');
            $table->string('table_key', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['branch_id', 'table_key']);
            $table->index(['business_id', 'type', 'is_active']);
        });

        Schema::create('analytics_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('qr_code_id')->nullable()->constrained('qr_codes')->nullOnDelete();
            $table->string('event_type', 24);
            $table->string('locale', 10)->nullable();
            $table->string('device_class', 16);
            $table->char('visitor_hash', 64);
            $table->string('subject_type', 32)->nullable();
            $table->string('subject_public_id', 32)->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index(['business_id', 'event_type', 'occurred_at'], 'analytics_business_event_time');
            $table->index(['qr_code_id', 'visitor_hash', 'occurred_at'], 'analytics_qr_deduplication');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('qr_codes');
    }
};
