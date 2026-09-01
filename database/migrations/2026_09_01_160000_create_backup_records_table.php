<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_records', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('type', 20);
            $table->string('status', 20);
            $table->string('disk');
            $table->string('path')->nullable();
            $table->char('checksum', 64)->nullable();
            $table->boolean('storage_encrypted');
            $table->unsignedBigInteger('size')->nullable();
            $table->json('manifest_json')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'completed_at']);
            $table->index(['type', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_records');
    }
};
