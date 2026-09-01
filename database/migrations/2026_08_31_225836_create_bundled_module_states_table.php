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
        Schema::create('bundled_module_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('module_key', 96);
            $table->string('module_version', 32);
            $table->unsignedInteger('schema_version');
            $table->boolean('is_enabled')->default(false);
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'module_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundled_module_states');
    }
};
