<?php

namespace Tests\Feature\Api\Admin\V1\Menu;

use App\Core\Authorization\FoundationRole;
use App\Core\Authorization\ProvisionFoundationRbac;
use App\Core\Business\Models\Branch;
use App\Core\Business\Models\Business;
use App\Core\Media\MediaStatus;
use App\Core\Media\Models\Media;
use App\Core\Menu\Actions\CreateMenuCategory;
use App\Core\Menu\Actions\CreateProduct;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\Models\Product;
use App\Core\Menu\PublicationState;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MenuManagementControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_product_listing_keeps_records_beyond_first_page_accessible(): void
    {
        [$business, $actor] = $this->context();
        $category = $this->category($business, $actor, 'coffee', 0);
        for ($position = 0; $position < 51; $position++) {
            $this->product($category, $actor, 'coffee-'.$position, 0);
        }
        Sanctum::actingAs($actor);

        $this->getJson('/api/admin/v1/products?page=1')
            ->assertJsonCount(50, 'data.data')
            ->assertJsonPath('data.last_page', 2)
            ->assertJsonPath('data.total', 51);
        $this->getJson('/api/admin/v1/products?page=2')
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.current_page', 2)
            ->assertJsonPath('data.data.0.slug', 'coffee-50');
    }

    public function test_content_editor_can_update_product_but_branch_price_write_is_forbidden(): void
    {
        [$business, $actor] = $this->context();
        $branch = Branch::factory()->for($business)->create();
        $category = $this->category($business, $actor, 'coffee', 0);
        $product = $this->product($category, $actor, 'coffee', 0);
        $editor = User::factory()->for($business)->create();
        app(ProvisionFoundationRbac::class)->execute($business);
        app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);
        $editor->assignRole(FoundationRole::ContentEditor->value);
        Sanctum::actingAs($editor);

        $this->putJson("/api/admin/v1/products/{$product->public_id}", [
            'category_id' => $category->public_id,
            'slug' => 'edited-coffee',
            'position' => 0,
            'translations' => $this->translations('قهوه', 'Coffee', 'قهوة'),
        ])->assertJsonPath('data.slug', 'edited-coffee');
        $this->putJson("/api/admin/v1/products/{$product->public_id}/branches/{$branch->id}/settings", [
            'price_amount' => 100,
            'availability_state' => 'available',
            'expected_version' => 0,
        ])->assertForbidden();

        $this->assertSame('edited-coffee', $product->fresh()->slug);
        $this->assertDatabaseMissing('product_branch_settings', ['product_id' => $product->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'menu.product.updated']);
    }

    public function test_updates_category_and_reorders_categories(): void
    {
        [$business, $actor] = $this->context();
        $coffee = $this->category($business, $actor, 'coffee', 0);
        $juice = $this->category($business, $actor, 'juice', 1);
        Sanctum::actingAs($actor);

        $this->putJson("/api/admin/v1/categories/{$coffee->public_id}", [
            'slug' => 'specialty-coffee',
            'position' => 0,
            'is_featured' => true,
            'translations' => $this->translations('قهوه تخصصی', 'Specialty Coffee', 'قهوة مختصة'),
        ])->assertOk()
            ->assertJsonPath('data.slug', 'specialty-coffee')
            ->assertJsonPath('data.is_featured', true);
        $this->putJson('/api/admin/v1/categories/order', ['categories' => [$juice->public_id, $coffee->public_id]])->assertNoContent();

        $this->assertSame(0, $juice->fresh()->position);
        $this->assertSame(1, $coffee->fresh()->position);
        $this->assertDatabaseHas('audit_logs', ['action' => 'menu.category.updated']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'menu.categories.reordered']);
    }

    public function test_updates_product_assigns_business_media_and_reorders_products(): void
    {
        [$business, $actor] = $this->context();
        $category = $this->category($business, $actor, 'coffee', 0);
        $espresso = $this->product($category, $actor, 'espresso', 0);
        $latte = $this->product($category, $actor, 'latte', 1);
        $media = $this->media($business);
        Sanctum::actingAs($actor);

        $this->putJson("/api/admin/v1/products/{$espresso->public_id}", [
            'category_id' => $category->public_id,
            'primary_media_id' => $media->public_id,
            'slug' => 'double-espresso',
            'position' => 0,
            'is_best_seller' => true,
            'translations' => $this->translations('اسپرسو دوبل', 'Double Espresso', 'إسبريسو مزدوج'),
        ])->assertOk()
            ->assertJsonPath('data.slug', 'double-espresso')
            ->assertJsonPath('data.primary_media_id', $media->id)
            ->assertJsonPath('data.is_best_seller', true);
        $this->putJson('/api/admin/v1/products/order', [
            'category_id' => $category->public_id,
            'products' => [$latte->public_id, $espresso->public_id],
        ])->assertNoContent();

        $this->assertSame(0, $latte->fresh()->position);
        $this->assertSame(1, $espresso->fresh()->position);
        $this->assertDatabaseHas('audit_logs', ['action' => 'menu.product.updated']);
    }

    public function test_delete_archives_managed_product_and_deletes_unused_draft_product(): void
    {
        [$business, $actor] = $this->context();
        $category = $this->category($business, $actor, 'coffee', 0);
        $managed = $this->product($category, $actor, 'espresso', 0);
        $managed->update(['publication_state' => PublicationState::Published]);
        $draft = $this->product($category, $actor, 'draft-drink', 1);
        Sanctum::actingAs($actor);

        $this->deleteJson("/api/admin/v1/products/{$managed->public_id}")
            ->assertOk()->assertJsonPath('data.result', 'archived');
        $this->deleteJson("/api/admin/v1/products/{$draft->public_id}")
            ->assertOk()->assertJsonPath('data.result', 'deleted');

        $this->assertSame(PublicationState::Archived, $managed->fresh()->publication_state);
        $this->assertDatabaseMissing('products', ['id' => $draft->id]);
    }

    public function test_rejects_category_deletion_while_products_exist(): void
    {
        [$business, $actor] = $this->context();
        $category = $this->category($business, $actor, 'coffee', 0);
        $this->product($category, $actor, 'espresso', 0);
        Sanctum::actingAs($actor);

        $this->deleteJson("/api/admin/v1/categories/{$category->public_id}")
            ->assertConflict();

        $this->assertModelExists($category);
    }

    public function test_updates_branch_price_and_availability_without_changing_publication_state(): void
    {
        [$business, $actor] = $this->context();
        $branch = Branch::factory()->for($business)->create();
        $category = $this->category($business, $actor, 'coffee', 0);
        $product = $this->product($category, $actor, 'espresso', 0);
        $product->update(['publication_state' => PublicationState::Published]);
        Sanctum::actingAs($actor);

        $this->putJson("/api/admin/v1/products/{$product->public_id}/branches/{$branch->id}/settings", [
            'price_amount' => 1_850_000,
            'availability_state' => 'sold_out',
            'expected_version' => 0,
        ])->assertOk()
            ->assertJsonPath('data.price_amount', 1_850_000)
            ->assertJsonPath('data.availability_state', 'sold_out');

        $this->assertSame(PublicationState::Published, $product->fresh()->publication_state);
    }

    public function test_business_user_cannot_discover_or_mutate_another_business_menu_records(): void
    {
        [$otherBusiness, $godfather] = $this->context();
        $otherBranch = Branch::factory()->for($otherBusiness)->create();
        $otherCategory = $this->category($otherBusiness, $godfather, 'private-category', 0);
        $otherProduct = $this->product($otherCategory, $godfather, 'private-product', 0);

        $business = Business::factory()->create();
        $user = User::factory()->for($business)->create();
        app(ProvisionFoundationRbac::class)->execute($business);
        app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);
        $user->assignRole(FoundationRole::BusinessOwner->value);
        Sanctum::actingAs($user);

        $this->getJson("/api/admin/v1/products/{$otherProduct->public_id}")->assertNotFound();
        $this->deleteJson("/api/admin/v1/products/{$otherProduct->public_id}")->assertNotFound();
        $this->deleteJson("/api/admin/v1/categories/{$otherCategory->public_id}")->assertNotFound();
        $this->putJson("/api/admin/v1/products/{$otherProduct->public_id}/branches/{$otherBranch->id}/settings", [
            'price_amount' => 1,
            'availability_state' => 'available',
            'expected_version' => 0,
        ])->assertNotFound();

        $this->assertModelExists($otherCategory);
        $this->assertModelExists($otherProduct);
    }

    /** @return array{Business, User} */
    private function context(): array
    {
        $business = Business::factory()->create(['slug' => 'denardi']);
        $actor = User::factory()->godfather()->create();

        return [$business, $actor];
    }

    private function category(Business $business, User $actor, string $slug, int $position): MenuCategory
    {
        return app(CreateMenuCategory::class)->execute($business, $actor, $slug, $position, $this->translations('دسته', 'Category', 'فئة'));
    }

    private function product(MenuCategory $category, User $actor, string $slug, int $position): Product
    {
        return app(CreateProduct::class)->execute($category, $actor, $slug, $position, $this->translations('محصول', 'Product', 'منتج'));
    }

    private function media(Business $business): Media
    {
        return Media::query()->create([
            'public_id' => (string) Str::ulid(),
            'business_id' => $business->id,
            'disk' => 'public',
            'path' => 'demo/original.jpg',
            'mime' => 'image/jpeg',
            'size' => 100,
            'checksum' => hash('sha256', 'demo'),
            'status' => MediaStatus::Ready,
        ]);
    }

    /** @return array<string, array<string, string>> */
    private function translations(string $fa, string $en, string $ar): array
    {
        return [
            'fa' => ['name' => $fa, 'translation_state' => 'ready'],
            'en' => ['name' => $en, 'translation_state' => 'ready'],
            'ar' => ['name' => $ar, 'translation_state' => 'ready'],
        ];
    }
}
