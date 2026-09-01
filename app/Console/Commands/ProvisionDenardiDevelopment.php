<?php

namespace App\Console\Commands;

use App\Core\Authorization\ProvisionFoundationRbac;
use App\Core\Business\Models\Branch;
use App\Core\Business\Models\Business;
use App\Core\Cms\Actions\CreateBlock;
use App\Core\Cms\Actions\CreatePage;
use App\Core\Cms\Actions\PublishPage;
use App\Core\Cms\Models\Page;
use App\Core\Instance\EnsureInstanceMetadata;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\Models\BusinessLocale;
use App\Core\Menu\Actions\ChangeCategoryPublicationState;
use App\Core\Menu\Actions\ChangeProductPublicationState;
use App\Core\Menu\Actions\CreateMenuCategory;
use App\Core\Menu\Actions\CreateProduct;
use App\Core\Menu\Actions\UpdateProductBranchSetting;
use App\Core\Menu\AvailabilityState;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\Models\Product;
use App\Core\Menu\PublicationState;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

#[Signature('denardi:provision-development')]
#[Description('Provision idempotent three-language Denardi development content')]
class ProvisionDenardiDevelopment extends Command
{
    public function handle(
        LocaleRegistry $locales,
        ProvisionFoundationRbac $rbac,
        EnsureInstanceMetadata $instance,
        CreatePage $createPage,
        CreateBlock $createBlock,
        PublishPage $publishPage,
        CreateMenuCategory $createCategory,
        ChangeCategoryPublicationState $publishCategory,
        CreateProduct $createProduct,
        ChangeProductPublicationState $publishProduct,
        UpdateProductBranchSetting $updateSetting,
    ): int {
        if (! app()->isLocal()) {
            throw new RuntimeException('Development content can only be provisioned in the local environment.');
        }

        $actor = User::query()->where('is_godfather', true)->firstOrFail();
        $business = Business::query()->updateOrCreate(
            ['slug' => (string) config('denardi.business_slug')],
            ['name' => 'Denardi', 'default_locale' => 'fa', 'timezone' => 'Asia/Tehran', 'is_active' => true],
        );
        foreach ($locales->all() as $locale => $metadata) {
            BusinessLocale::query()->updateOrCreate(
                ['business_id' => $business->id, 'locale' => $locale],
                [
                    'direction' => $metadata['direction'],
                    'name' => $metadata['name'],
                    'native_name' => $metadata['native_name'],
                    'is_default' => $locale === 'fa',
                    'is_enabled' => true,
                    'is_required_for_publication' => true,
                ],
            );
        }
        $branch = Branch::query()->updateOrCreate(
            ['business_id' => $business->id, 'slug' => 'central'],
            ['name' => 'Denardi Central', 'timezone' => 'Asia/Tehran', 'is_default' => true, 'is_active' => true],
        );
        $rbac->execute($business);
        $instance->execute()->update(['business_id' => $business->id]);

        $this->provisionPages($business, $actor, $createPage, $createBlock, $publishPage);
        $this->provisionMenu($business, $branch, $actor, $createCategory, $publishCategory, $createProduct, $publishProduct, $updateSetting);
        $this->components->info('Denardi development content is ready in fa/en/ar.');

        return self::SUCCESS;
    }

    private function provisionPages(Business $business, User $actor, CreatePage $createPage, CreateBlock $createBlock, PublishPage $publishPage): void
    {
        $pages = [
            'home' => ['landing', 'hero', ['alignment' => 'center'], [
                'fa' => ['title' => 'دناردی؛ هنر، قهوه و آبمیوه'],
                'en' => ['title' => 'Denardi · Art, Coffee and Juice'],
                'ar' => ['title' => 'ديناردي · الفن والقهوة والعصير'],
            ]],
            'about' => ['standard', 'about', [], [
                'fa' => ['heading' => 'درباره دناردی', 'body' => 'فضایی برای قهوه، هنر و تجربه‌های تازه.'],
                'en' => ['heading' => 'About Denardi', 'body' => 'A place for coffee, art and fresh experiences.'],
                'ar' => ['heading' => 'عن ديناردي', 'body' => 'مساحة للقهوة والفن والتجارب الجديدة.'],
            ]],
            'contact' => ['standard', 'contact', [], [
                'fa' => ['heading' => 'ارتباط با دناردی', 'body' => 'اطلاعات تماس نهایی در پنل مدیریت تکمیل می‌شود.'],
                'en' => ['heading' => 'Contact Denardi', 'body' => 'Final contact details can be completed in Admin.'],
                'ar' => ['heading' => 'تواصل مع ديناردي', 'body' => 'يمكن إكمال معلومات الاتصال النهائية في لوحة الإدارة.'],
            ]],
            'privacy' => ['standard', 'about', [], [
                'fa' => ['heading' => 'حریم خصوصی', 'body' => 'دناردی برای آمار پایه، IP خام ذخیره نمی‌کند.'],
                'en' => ['heading' => 'Privacy', 'body' => 'Denardi does not store raw IP addresses for basic analytics.'],
                'ar' => ['heading' => 'الخصوصية', 'body' => 'لا يخزن ديناردي عناوين IP الخام للتحليلات الأساسية.'],
            ]],
        ];

        foreach ($pages as $slug => [$template, $blockType, $structure, $content]) {
            if (Page::query()->whereBelongsTo($business)->where('slug', $slug)->exists()) {
                continue;
            }
            $pageTranslations = collect($content)->map(fn (array $translation): array => [
                'title' => $translation['title'] ?? $translation['heading'],
                'meta_title' => $translation['title'] ?? $translation['heading'],
                'meta_description' => $translation['body'] ?? null,
                'translation_state' => 'ready',
            ])->all();
            $blockTranslations = collect($content)->map(fn (array $translation): array => ['content' => $translation, 'translation_state' => 'ready'])->all();
            $page = $createPage->execute($business, $actor, $slug, $template, $pageTranslations);
            $createBlock->execute($page, $actor, $blockType, 0, $structure, $blockTranslations);
            $publishPage->execute($page->fresh(), $actor, 1);
        }
    }

    private function provisionMenu(
        Business $business,
        Branch $branch,
        User $actor,
        CreateMenuCategory $createCategory,
        ChangeCategoryPublicationState $publishCategory,
        CreateProduct $createProduct,
        ChangeProductPublicationState $publishProduct,
        UpdateProductBranchSetting $updateSetting,
    ): void {
        $category = MenuCategory::query()->whereBelongsTo($business)->where('slug', 'coffee')->first();
        if ($category === null) {
            $category = $createCategory->execute($business, $actor, 'coffee', 0, [
                'fa' => ['name' => 'قهوه', 'description' => 'قهوه‌های کلاسیک دناردی', 'translation_state' => 'ready'],
                'en' => ['name' => 'Coffee', 'description' => 'Denardi classic coffees', 'translation_state' => 'ready'],
                'ar' => ['name' => 'القهوة', 'description' => 'قهوة ديناردي الكلاسيكية', 'translation_state' => 'ready'],
            ]);
            $publishCategory->execute($category, $actor, PublicationState::Published);
        }
        $product = Product::query()->whereBelongsTo($business)->where('slug', 'espresso')->first();
        if ($product === null) {
            $product = $createProduct->execute($category, $actor, 'espresso', 0, [
                'fa' => ['name' => 'اسپرسو', 'description' => 'عصاره‌ای متعادل و خوش‌عطر', 'translation_state' => 'ready'],
                'en' => ['name' => 'Espresso', 'description' => 'A balanced and aromatic shot', 'translation_state' => 'ready'],
                'ar' => ['name' => 'إسبريسو', 'description' => 'جرعة متوازنة وغنية بالعطر', 'translation_state' => 'ready'],
            ], ['is_best_seller' => true]);
            $publishProduct->execute($product, $actor, PublicationState::Published);
            $updateSetting->execute($product, $branch, $actor, 1_200_000, AvailabilityState::Available, 0);
        }
    }
}
