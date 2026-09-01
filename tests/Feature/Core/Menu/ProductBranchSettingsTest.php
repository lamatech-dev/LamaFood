<?php

namespace Tests\Feature\Core\Menu;

use App\Core\Business\Models\Branch;
use App\Core\Business\Models\Business;
use App\Core\Menu\Actions\ChangeCategoryPublicationState;
use App\Core\Menu\Actions\ChangeProductPublicationState;
use App\Core\Menu\Actions\CreateMenuCategory;
use App\Core\Menu\Actions\CreateProduct;
use App\Core\Menu\Actions\UpdateProductBranchSetting;
use App\Core\Menu\AvailabilityState;
use App\Core\Menu\Models\Product;
use App\Core\Menu\PublicationState;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductBranchSettingsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_one_catalog_product_has_independent_branch_prices_and_availability(): void
    {
        $business = Business::factory()->create(['slug' => 'denardi']);
        $main = Branch::factory()->for($business)->create(['is_default' => true]);
        $second = Branch::factory()->for($business)->create();
        $actor = User::factory()->for($business)->create();
        $translations = [
            'fa' => ['name' => 'قهوه', 'translation_state' => 'ready'],
            'en' => ['name' => 'Coffee', 'translation_state' => 'ready'],
            'ar' => ['name' => 'قهوة', 'translation_state' => 'ready'],
        ];
        $category = app(CreateMenuCategory::class)->execute($business, $actor, 'coffee', 0, $translations);
        app(ChangeCategoryPublicationState::class)->execute($category, $actor, PublicationState::Published);
        $product = app(CreateProduct::class)->execute($category, $actor, 'latte', 0, $translations);
        app(ChangeProductPublicationState::class)->execute($product, $actor, PublicationState::Published);

        app(UpdateProductBranchSetting::class)->execute($product, $main, $actor, 2_500_000, AvailabilityState::Available, 0);
        app(UpdateProductBranchSetting::class)->execute($product, $second, $actor, 2_700_000, AvailabilityState::SoldOut, 0);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('product_branch_settings', 2);
        $this->assertSame(2_500_000, $product->branchSettings()->whereBelongsTo($main)->value('price_amount'));
        $this->assertSame(AvailabilityState::SoldOut, $product->branchSettings()->whereBelongsTo($second)->firstOrFail()->availability_state);
        $this->assertSame(PublicationState::Published, Product::query()->findOrFail($product->id)->publication_state);
    }

    public function test_public_menu_uses_current_locale_and_branch_setting_without_fallback(): void
    {
        $this->withoutVite();
        $business = Business::factory()->create(['slug' => 'denardi']);
        $branch = Branch::factory()->for($business)->create(['is_default' => true]);
        $actor = User::factory()->for($business)->create();
        $category = app(CreateMenuCategory::class)->execute($business, $actor, 'coffee', 0, [
            'fa' => ['name' => 'قهوه', 'translation_state' => 'ready'],
            'en' => ['name' => 'Coffee', 'translation_state' => 'ready'],
            'ar' => ['name' => 'القهوة', 'translation_state' => 'ready'],
        ]);
        app(ChangeCategoryPublicationState::class)->execute($category, $actor, PublicationState::Published);
        $product = app(CreateProduct::class)->execute($category, $actor, 'latte', 0, [
            'fa' => ['name' => 'لاته فارسی', 'translation_state' => 'ready'],
            'en' => ['name' => 'English Latte', 'translation_state' => 'ready'],
            'ar' => ['name' => 'لاتيه عربي', 'translation_state' => 'ready'],
        ]);
        app(ChangeProductPublicationState::class)->execute($product, $actor, PublicationState::Published);
        app(UpdateProductBranchSetting::class)->execute($product, $branch, $actor, 2_500_000, AvailabilityState::SoldOut, 0);

        $this->get('/ar/menu')->assertOk()->assertSee('لاتيه عربي')->assertSee('غير متوفر')->assertDontSee('لاته فارسی');
    }
}
