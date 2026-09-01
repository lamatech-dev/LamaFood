<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instance_metadata', function (Blueprint $table) {
            $table->id();
            $table->ulid('instance_id')->unique();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('license_id')->nullable()->index();
            $table->string('install_channel', 32)->default('managed');
            $table->string('core_version', 32);
            $table->unsignedInteger('schema_version')->default(1);
            $table->timestamp('last_health_check_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instance_metadata');
    }
};
