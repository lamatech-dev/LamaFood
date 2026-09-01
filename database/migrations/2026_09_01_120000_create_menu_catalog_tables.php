<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_categories')->nullOnDelete();
            $table->string('slug');
            $table->unsignedInteger('position')->default(0);
            $table->string('publication_state', 16)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->unique(['business_id', 'slug']);
            $table->index(['business_id', 'publication_state', 'position']);
        });

        Schema::create('menu_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('menu_categories')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('translation_state', 16)->default('draft');
            $table->timestamps();
            $table->unique(['category_id', 'locale']);
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('menu_categories')->restrictOnDelete();
            $table->foreignId('primary_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('slug');
            $table->string('publication_state', 16)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_best_seller')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['business_id', 'slug']);
            $table->index(['business_id', 'category_id', 'publication_state', 'position'], 'product_catalog_index');
        });

        Schema::create('product_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->text('ingredients')->nullable();
            $table->text('allergen_notice')->nullable();
            $table->string('translation_state', 16)->default('draft');
            $table->timestamps();
            $table->unique(['product_id', 'locale']);
        });

        Schema::create('product_branch_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('price_amount');
            $table->string('availability_state', 16)->default('available');
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'branch_id']);
            $table->index(['branch_id', 'availability_state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_branch_settings');
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('menu_category_translations');
        Schema::dropIfExists('menu_categories');
    }
};
