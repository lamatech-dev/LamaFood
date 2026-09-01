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
        Schema::create('business_locales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('direction', 3);
            $table->string('name', 64);
            $table->string('native_name', 64);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_required_for_publication')->default(true);
            $table->timestamps();

            $table->unique(['business_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_locales');
    }
};
