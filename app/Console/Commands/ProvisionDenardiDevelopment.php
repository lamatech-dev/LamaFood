<?php

namespace App\Console\Commands;

use App\Core\Authorization\ProvisionFoundationRbac;
use App\Core\Business\Models\Branch;
use App\Core\Business\Models\Business;
use App\Core\Cms\Actions\CreateBlock;
use App\Core\Cms\Actions\CreatePage;
use App\Core\Cms\Actions\PublishPage;
use App\Core\Cms\Models\Block;
use App\Core\Cms\Models\Page;
use App\Core\Instance\EnsureInstanceMetadata;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\Models\BusinessLocale;
use App\Core\Media\MediaDerivativeGenerator;
use App\Core\Media\MediaStatus;
use App\Core\Media\Models\Media;
use App\Core\Media\Models\MediaUsage;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

#[Signature('denardi:provision-development {--reset : Remove and rebuild owned local demo content} {--remove : Remove owned local demo content without rebuilding}')]
#[Description('Provision idempotent three-language Denardi development content')]
class ProvisionDenardiDevelopment extends Command
{
    /** @var array<string, list<string>> */
    private array $demoRecords = ['pages' => [], 'categories' => [], 'products' => [], 'media' => []];

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
        $manifest = Storage::disk('local')->get('demo/denardi-'.$business->id.'.json');
        $recorded = $manifest ? json_decode($manifest, true, 512, JSON_THROW_ON_ERROR) : [];
        foreach (array_keys($this->demoRecords) as $type) {
            $this->demoRecords[$type] = array_values(array_filter($recorded[$type] ?? [], 'is_string'));
        }
        if ($this->option('remove')) {
            $this->resetDemoContent($business);
            $this->saveManifest($business);

            return self::SUCCESS;
        }
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

        if ($this->option('reset')) {
            $this->resetDemoContent($business);
        }

        $media = $this->provisionMedia($business, $actor);
        $this->provisionPages($business, $actor, $media, $createPage, $createBlock, $publishPage);
        $this->provisionMenu($business, $branch, $actor, $media, $createCategory, $publishCategory, $createProduct, $publishProduct, $updateSetting);
        $this->saveManifest($business);
        $this->components->info('Denardi development content is ready in fa/en/ar.');

        return self::SUCCESS;
    }

    /** @param array<string, Media> $media */
    private function provisionPages(Business $business, User $actor, array $media, CreatePage $createPage, CreateBlock $createBlock, PublishPage $publishPage): void
    {
        $pages = [
            'home' => ['landing', [
                ['hero', ['mediaId' => $media['coffee']->id, 'alignment' => 'end'], [
                    'fa' => ['eyebrow' => 'ART · COFFEE · JUICE', 'title' => 'دناردی؛ مکثی برای طعم و هنر', 'body' => 'یک تجربه‌ی شهری برای قهوه‌های دقیق، نوشیدنی‌های تازه و لحظه‌هایی که ماندگار می‌شوند.', 'ctaLabel' => 'دیدن منو'],
                    'en' => ['eyebrow' => 'ART · COFFEE · JUICE', 'title' => 'Denardi, a pause for taste and art', 'body' => 'An urban experience shaped by precise coffee, fresh drinks and memorable pauses.', 'ctaLabel' => 'Explore the menu'],
                    'ar' => ['eyebrow' => 'ART · COFFEE · JUICE', 'title' => 'ديناردي، لحظة للطعم والفن', 'body' => 'تجربة حضرية تجمع القهوة المتقنة والمشروبات الطازجة واللحظات التي تبقى.', 'ctaLabel' => 'اكتشف القائمة'],
                ]],
                ['about', [], [
                    'fa' => ['heading' => 'طعم، با ریتم آرام‌تر', 'body' => 'این محتوای نمایشی، نگاه دناردی به کیفیت، جزئیات و مهمان‌نوازی معاصر را برای ارزیابی طراحی نشان می‌دهد.'],
                    'en' => ['heading' => 'Taste, at a slower rhythm', 'body' => 'This demo content presents a considered view of quality, detail and contemporary hospitality for design evaluation.'],
                    'ar' => ['heading' => 'الطعم، بإيقاع أكثر هدوءاً', 'body' => 'يعرض هذا المحتوى التجريبي رؤية مدروسة للجودة والتفاصيل والضيافة المعاصرة لتقييم التصميم.'],
                ]],
                ['menu_preview', ['variant' => 'editorial'], [
                    'fa' => ['heading' => 'از اسپرسوی کلاسیک تا ترکیب‌های تازه', 'intro' => 'منوی نمایشی سه‌زبانه را مرور کنید و وضعیت‌های موجود، ناموجود و پیشنهادی را ببینید.', 'ctaLabel' => 'ورود به منو'],
                    'en' => ['heading' => 'From classic espresso to fresh blends', 'intro' => 'Browse the three-language demo menu with available, sold-out and featured states.', 'ctaLabel' => 'Open the menu'],
                    'ar' => ['heading' => 'من الإسبريسو الكلاسيكي إلى الخلطات الطازجة', 'intro' => 'تصفّح القائمة التجريبية بثلاث لغات مع حالات التوفر والنفاد والاختيارات المميزة.', 'ctaLabel' => 'افتح القائمة'],
                ]],
            ]],
            'about' => ['standard', [
                ['about', [], [
                    'fa' => ['heading' => 'درباره دناردی', 'body' => 'دناردی در این نسخه‌ی نمایشی، یک کافه‌ی معاصر با تمرکز بر قهوه، آبمیوه، هنر و تجربه‌ی دقیق مهمان است. متن نهایی برند بعداً در پنل مدیریت جایگزین می‌شود.'],
                    'en' => ['heading' => 'About Denardi', 'body' => 'In this demo, Denardi is presented as a contemporary café focused on coffee, juice, art and a carefully considered guest experience. Final brand copy will be entered in Admin.'],
                    'ar' => ['heading' => 'عن ديناردي', 'body' => 'يُقدَّم ديناردي في هذا العرض كمقهى معاصر يهتم بالقهوة والعصائر والفن وتجربة الضيف المتقنة. سيُستبدل النص النهائي من لوحة الإدارة.'],
                ]],
                ['gallery', ['mediaIds' => [$media['cold-drinks']->id, $media['fresh-juice']->id, $media['pastry']->id], 'layout' => 'editorial'], [
                    'fa' => ['heading' => 'جزئیات، بخشی از تجربه‌اند', 'caption' => 'تصاویر صرفاً برای نمایش طراحی و قابل جایگزینی با عکاسی واقعی دناردی هستند.'],
                    'en' => ['heading' => 'Details shape the experience', 'caption' => 'Images are for design demonstration only and can be replaced with Denardi photography.'],
                    'ar' => ['heading' => 'التفاصيل تصنع التجربة', 'caption' => 'الصور مخصصة لعرض التصميم فقط ويمكن استبدالها بصور ديناردي الفعلية.'],
                ]],
            ]],
            'contact' => ['standard', [[
                'contact', ['variant' => 'demo', 'phone' => '+00 000 000 0000', 'instagramUrl' => 'https://example.com/denardi-demo'], [
                    'fa' => ['heading' => 'ارتباط با دناردی', 'body' => "نشانی نمونه: خیابان نمونه، گذر هنر، پلاک ۱۲\nساعت نمونه: هر روز، ۸ صبح تا ۱۰ شب\nاین اطلاعات فقط برای ارزیابی طراحی است؛ برای تماس یا مراجعه استفاده نکنید.", 'phoneLabel' => 'تلفن نمایشی — غیرقابل تماس', 'instagramLabel' => 'شبکه اجتماعی نمایشی'],
                    'en' => ['heading' => 'Contact Denardi', 'body' => "Demo address: 12 Sample Street, Art Passage\nDemo hours: daily, 8 am–10 pm\nFor design evaluation only. Do not use these details to call or visit.", 'phoneLabel' => 'Demo phone — not callable', 'instagramLabel' => 'Demo social profile'],
                    'ar' => ['heading' => 'تواصل مع ديناردي', 'body' => "عنوان تجريبي: ١٢ شارع المثال، ممر الفن\nساعات تجريبية: يومياً من ٨ صباحاً إلى ١٠ مساءً\nلتقييم التصميم فقط. لا تستخدم هذه المعلومات للاتصال أو الزيارة.", 'phoneLabel' => 'هاتف تجريبي — غير صالح للاتصال', 'instagramLabel' => 'حساب اجتماعي تجريبي'],
                ],
            ]]],
            'privacy' => ['standard', [[
                'about', [], [
                    'fa' => ['heading' => 'حریم خصوصی', 'body' => 'دناردی برای آمار پایه، IP خام ذخیره نمی‌کند و داده‌های بازدید به‌شکل حداقلی و محافظت‌شده پردازش می‌شوند.'],
                    'en' => ['heading' => 'Privacy', 'body' => 'Denardi does not store raw IP addresses for basic analytics; visit data is processed minimally and with privacy safeguards.'],
                    'ar' => ['heading' => 'الخصوصية', 'body' => 'لا يخزن ديناردي عناوين IP الخام للتحليلات الأساسية، وتُعالج بيانات الزيارة بأقل قدر ممكن ومع ضمانات للخصوصية.'],
                ],
            ]]],
        ];

        foreach ($pages as $slug => [$template, $blocks]) {
            if (Page::query()->whereBelongsTo($business)->where('slug', $slug)->exists()) {
                continue;
            }

            $firstContent = $blocks[0][2];
            $pageTranslations = collect($firstContent)->map(fn (array $translation): array => [
                'title' => $translation['title'] ?? $translation['heading'],
                'meta_title' => $translation['title'] ?? $translation['heading'],
                'meta_description' => $translation['body'],
                'translation_state' => 'ready',
            ])->all();
            $page = $createPage->execute($business, $actor, $slug, $template, $pageTranslations);
            $this->demoRecords['pages'][] = $page->public_id;

            foreach ($blocks as $position => [$type, $structure, $content]) {
                $translations = collect($content)->map(fn (array $translation): array => ['content' => $translation, 'translation_state' => 'ready'])->all();
                $createBlock->execute($page, $actor, $type, $position, $structure, $translations);
            }

            $publishPage->execute($page->fresh(), $actor, count($blocks));
        }
    }

    /** @param array<string, Media> $media */
    private function provisionMenu(
        Business $business,
        Branch $branch,
        User $actor,
        array $media,
        CreateMenuCategory $createCategory,
        ChangeCategoryPublicationState $publishCategory,
        CreateProduct $createProduct,
        ChangeProductPublicationState $publishProduct,
        UpdateProductBranchSetting $updateSetting,
    ): void {
        $categories = [
            'coffee' => [0, ['قهوه کلاسیک', 'Classic Coffee', 'القهوة الكلاسيكية'], ['عصاره‌گیری دقیق و طعم‌های آشنا', 'Precise extraction, familiar flavours', 'استخلاص متقن ونكهات مألوفة']],
            'signature' => [1, ['امضای دناردی', 'Denardi Signatures', 'توقيعات ديناردي'], ['ترکیب‌های ویژه برای تجربه‌ای متفاوت', 'Distinctive recipes for a different pause', 'وصفات مميزة للحظة مختلفة']],
            'cold-drinks' => [2, ['قهوه سرد', 'Cold Coffee', 'القهوة الباردة'], ['خنک، شفاف و پرانرژی', 'Chilled, bright and energising', 'باردة ومنعشة ومفعمة بالحيوية']],
            'fresh-juice' => [3, ['آبمیوه طبیعی', 'Fresh Juice', 'العصائر الطازجة'], ['فشرده‌شده با میوه‌های منتخب', 'Pressed from selected fruit', 'معصورة من فواكه مختارة']],
            'tea' => [4, ['چای و دمنوش', 'Tea & Infusions', 'الشاي والمنقوعات'], ['آرام، معطر و مناسب یک مکث', 'Calm, aromatic and made for a pause', 'هادئة وعطرية للحظات الاسترخاء']],
            'breakfast' => [5, ['صبحانه و میان‌وعده', 'Breakfast & Bites', 'الفطور والوجبات الخفيفة'], ['بشقاب‌های سبک برای شروع یا ادامه روز', 'Light plates for any part of the day', 'أطباق خفيفة لكل أوقات اليوم']],
            'pastry' => [6, ['شیرینی و دسر', 'Pastry & Dessert', 'المعجنات والحلويات'], ['پخت روز و شیرینی‌های دست‌ساز', 'Fresh bakes and handmade sweets', 'مخبوزات يومية وحلويات مصنوعة يدوياً']],
            'seasonal' => [7, ['فصل محدود', 'Seasonal Editions', 'إصدارات موسمية'], ['ایده‌های تازه با ریتم فصل', 'Fresh ideas moving with the season', 'أفكار جديدة تتناغم مع الموسم']],
        ];
        $categoryModels = [];
        foreach ($categories as $slug => [$position, $names, $descriptions]) {
            $category = MenuCategory::query()->whereBelongsTo($business)->where('slug', $slug)->first();
            if ($category === null) {
                $category = $createCategory->execute($business, $actor, $slug, $position, $this->menuTranslations($names, $descriptions), isFeatured: $position < 2);
                $this->demoRecords['categories'][] = $category->public_id;
                $publishCategory->execute($category, $actor, PublicationState::Published);
            }
            $categoryModels[$slug] = $category;
        }

        $products = [
            ['coffee', 'espresso', 0, 1_200_000, 'coffee', AvailabilityState::Available, PublicationState::Published, ['اسپرسو', 'Espresso', 'إسبريسو'], ['عصاره‌ای متعادل با پایان شکلاتی', 'Balanced with a chocolate finish', 'متوازن بنهاية شوكولاتية'], ['is_best_seller' => true]],
            ['coffee', 'americano', 1, 1_450_000, null, AvailabilityState::Available, PublicationState::Published, ['آمریکانو', 'Americano', 'أمريكانو'], ['اسپرسو و آب داغ، شفاف و بلند', 'Espresso lengthened with hot water', 'إسبريسو مع الماء الساخن بنكهة صافية'], []],
            ['coffee', 'cappuccino', 2, 1_850_000, 'coffee', AvailabilityState::Available, PublicationState::Published, ['کاپوچینو', 'Cappuccino', 'كابتشينو'], ['اسپرسو، شیر و فوم مخملی', 'Espresso, milk and velvet foam', 'إسبريسو وحليب ورغوة مخملية'], ['is_featured' => true]],
            ['signature', 'pistachio-latte', 0, 2_650_000, 'coffee', AvailabilityState::Available, PublicationState::Published, ['لاته پسته', 'Pistachio Latte', 'لاتيه الفستق'], ['پسته برشته، اسپرسو و شیر', 'Roasted pistachio, espresso and milk', 'فستق محمص وإسبريسو وحليب'], ['is_new' => true, 'is_featured' => true]],
            ['signature', 'saffron-cold-brew', 1, 2_450_000, 'cold-drinks', AvailabilityState::Available, PublicationState::Published, ['کلدبرو زعفران', 'Saffron Cold Brew', 'كولد برو بالزعفران'], ['قهوه سرد با رایحه‌ی ظریف زعفران', 'Cold brew with a subtle saffron aroma', 'قهوة باردة بعبق الزعفران الرقيق'], ['is_best_seller' => true]],
            ['signature', 'rose-tonic', 2, 2_250_000, null, AvailabilityState::SoldOut, PublicationState::Published, ['رز تونیک', 'Rose Tonic', 'تونيك الورد'], ['تونیک، رز و مرکبات روشن', 'Tonic, rose and bright citrus', 'تونيك وورد وحمضيات منعشة'], []],
            ['cold-drinks', 'iced-latte', 0, 2_050_000, 'cold-drinks', AvailabilityState::Available, PublicationState::Published, ['آیس لاته', 'Iced Latte', 'آيس لاتيه'], ['اسپرسو و شیر روی یخ شفاف', 'Espresso and milk over clear ice', 'إسبريسو وحليب فوق مكعبات الثلج'], ['is_best_seller' => true]],
            ['cold-drinks', 'cold-brew', 1, 2_150_000, null, AvailabilityState::Available, PublicationState::Published, ['کلدبرو', 'Cold Brew', 'كولد برو'], ['دم‌آوری طولانی با بافت نرم', 'Slow-steeped with a smooth finish', 'منقوع ببطء بنهاية ناعمة'], []],
            ['cold-drinks', 'espresso-tonic', 2, 2_300_000, 'cold-drinks', AvailabilityState::Available, PublicationState::Published, ['اسپرسو تونیک', 'Espresso Tonic', 'إسبريسو تونيك'], ['تلخی قهوه، حباب و مرکبات', 'Coffee bitterness, bubbles and citrus', 'مرارة القهوة وفقاعات ولمسة حمضية'], ['is_new' => true]],
            ['fresh-juice', 'orange-juice', 0, 2_200_000, 'fresh-juice', AvailabilityState::Available, PublicationState::Published, ['آب پرتقال', 'Orange Juice', 'عصير البرتقال'], ['تازه و بدون شکر افزوده', 'Fresh with no added sugar', 'طازج دون سكر مضاف'], ['is_best_seller' => true]],
            ['fresh-juice', 'pomegranate-juice', 1, 2_600_000, 'fresh-juice', AvailabilityState::Available, PublicationState::Published, ['آب انار', 'Pomegranate Juice', 'عصير الرمان'], ['ترش و شیرین با رنگ طبیعی', 'Tart, sweet and naturally vivid', 'حلو وحامض بلون طبيعي زاهٍ'], []],
            ['fresh-juice', 'green-blend', 2, 2_750_000, null, AvailabilityState::SoldOut, PublicationState::Published, ['ترکیب سبز', 'Green Blend', 'المزيج الأخضر'], ['سیب، خیار و سبزی‌های تازه', 'Apple, cucumber and fresh greens', 'تفاح وخيار وخضروات طازجة'], ['is_featured' => true]],
            ['tea', 'persian-tea', 0, 1_250_000, null, AvailabilityState::Available, PublicationState::Published, ['چای ایرانی', 'Persian Tea', 'شاي فارسي'], ['دم‌کشیده و خوش‌رنگ', 'Slow-steeped and richly coloured', 'مخمّر ببطء وغني اللون'], []],
            ['tea', 'hibiscus-infusion', 1, 1_650_000, 'fresh-juice', AvailabilityState::Available, PublicationState::Published, ['دمنوش چای ترش', 'Hibiscus Infusion', 'منقوع الكركديه'], ['ترش، میوه‌ای و خوش‌رنگ', 'Tart, fruity and ruby bright', 'حامض وفاكهي بلون ياقوتي'], ['is_new' => true]],
            ['tea', 'mint-lemon', 2, 1_550_000, null, AvailabilityState::Available, PublicationState::Published, ['نعنا و لیمو', 'Mint & Lemon', 'نعناع وليمون'], ['دمنوش سبک و معطر', 'A light aromatic infusion', 'منقوع خفيف وعطري'], []],
            ['breakfast', 'avocado-toast', 0, 3_450_000, null, AvailabilityState::Available, PublicationState::Published, ['تست آووکادو', 'Avocado Toast', 'توست الأفوكادو'], ['نان برشته، آووکادو و دانه‌ها', 'Toasted bread, avocado and seeds', 'خبز محمص وأفوكادو وبذور'], ['is_featured' => true]],
            ['breakfast', 'granola-bowl', 1, 2_950_000, 'pastry', AvailabilityState::Available, PublicationState::Published, ['کاسه گرانولا', 'Granola Bowl', 'وعاء الغرانولا'], ['ماست، گرانولا و میوه فصل', 'Yoghurt, granola and seasonal fruit', 'لبن وغرانولا وفاكهة موسمية'], []],
            ['breakfast', 'cheese-croissant', 2, 2_350_000, 'pastry', AvailabilityState::SoldOut, PublicationState::Published, ['کروسان پنیر', 'Cheese Croissant', 'كرواسون بالجبن'], ['کروسان گرم با پنیر نرم', 'Warm croissant with soft cheese', 'كرواسون دافئ مع جبن طري'], []],
            ['pastry', 'butter-croissant', 0, 1_100_000, 'pastry', AvailabilityState::Available, PublicationState::Published, ['کروسان کره‌ای', 'Butter Croissant', 'كرواسون بالزبدة'], ['لایه‌لایه و تازه‌پخت', 'Flaky and freshly baked', 'هشّ وطازج الخَبز'], ['is_best_seller' => true]],
            ['pastry', 'chocolate-cake', 1, 1_750_000, 'pastry', AvailabilityState::Available, PublicationState::Published, ['کیک شکلات تلخ', 'Dark Chocolate Cake', 'كعكة الشوكولاتة الداكنة'], ['کاکائوی عمیق و بافت مرطوب', 'Deep cocoa and a moist crumb', 'كاكاو غني وقوام طري'], ['is_featured' => true]],
            ['pastry', 'carrot-cake', 2, 1_850_000, null, AvailabilityState::Available, PublicationState::Published, ['کیک هویج', 'Carrot Cake', 'كعكة الجزر'], ['ادویه گرم و کرم پنیر', 'Warm spice and cream cheese', 'توابل دافئة وكريمة الجبن'], []],
            ['seasonal', 'summer-peach', 0, 2_700_000, 'fresh-juice', AvailabilityState::Available, PublicationState::Published, ['هلو تابستانی', 'Summer Peach', 'خوخ الصيف'], ['هلو، مرکبات و یخ', 'Peach, citrus and ice', 'خوخ وحمضيات وثلج'], ['is_new' => true]],
            ['seasonal', 'fig-latte', 1, 2_800_000, null, AvailabilityState::Available, PublicationState::Draft, ['لاته انجیر', 'Fig Latte', 'لاتيه التين'], ['محصول آزمایشی برای نمایش حالت پیش‌نویس', 'A demo item showing the draft state', 'عنصر تجريبي لعرض حالة المسودة'], []],
            ['seasonal', 'winter-cocoa', 2, 2_400_000, null, AvailabilityState::SoldOut, PublicationState::Inactive, ['کاکائو زمستانی', 'Winter Cocoa', 'كاكاو الشتاء'], ['محصول نمایشی غیرفعال', 'A demo item showing the inactive state', 'عنصر تجريبي لعرض الحالة غير النشطة'], []],
        ];

        foreach ($products as [$categorySlug, $slug, $position, $price, $mediaKey, $availability, $publication, $names, $descriptions, $flags]) {
            if (Product::query()->whereBelongsTo($business)->where('slug', $slug)->exists()) {
                continue;
            }
            $mediaKey = match ($slug) {
                'cappuccino', 'pistachio-latte' => 'latte',
                'persian-tea', 'mint-lemon' => 'tea',
                'avocado-toast' => 'avocado',
                'granola-bowl' => 'granola',
                'saffron-cold-brew', 'espresso-tonic', 'hibiscus-infusion', 'summer-peach' => null,
                default => $mediaKey,
            };
            $product = $createProduct->execute($categoryModels[$categorySlug], $actor, $slug, $position, $this->menuTranslations($names, $descriptions), $flags, $mediaKey ? $media[$mediaKey] : null);
            $this->demoRecords['products'][] = $product->public_id;
            if ($publication !== PublicationState::Draft) {
                $publishProduct->execute($product, $actor, $publication);
            }
            $updateSetting->execute($product, $branch, $actor, $price, $availability, 0);
        }
    }

    private function resetDemoContent(Business $business): void
    {
        DB::transaction(function () use ($business): void {
            $pageIds = Page::query()->whereBelongsTo($business)->whereIn('public_id', $this->demoRecords['pages'])->pluck('id');
            $blockIds = Block::query()->whereIn('page_id', $pageIds)->pluck('id');
            MediaUsage::query()->where('subject_type', (new Block)->getMorphClass())->whereIn('subject_id', $blockIds)->delete();
            Product::query()->whereBelongsTo($business)->whereIn('public_id', $this->demoRecords['products'])->delete();
            MenuCategory::query()->whereBelongsTo($business)->whereIn('public_id', $this->demoRecords['categories'])->doesntHave('products')->delete();
            Page::query()->whereBelongsTo($business)->whereIn('id', $pageIds)->delete();
            Media::query()->whereBelongsTo($business)->whereIn('public_id', $this->demoRecords['media'])->doesntHave('products')->doesntHave('usages')->delete();

            $this->demoRecords['pages'] = [];
            $this->demoRecords['products'] = [];
            $this->demoRecords['categories'] = MenuCategory::query()->whereBelongsTo($business)->whereIn('public_id', $this->demoRecords['categories'])->pluck('public_id')->all();
            $this->demoRecords['media'] = Media::query()->whereBelongsTo($business)->whereIn('public_id', $this->demoRecords['media'])->pluck('public_id')->all();
        });

        $this->components->warn('Owned demo records removed; unrelated content and shared media/categories preserved. Source assets remain in public/demo/denardi.');
    }

    /** @return array<string, Media> */
    private function provisionMedia(Business $business, User $actor): array
    {
        $assets = [
            'coffee' => ['coffee.webp', ['فنجان اسپرسو در فضای تیره', 'Espresso in a dark café setting', 'فنجان إسبريسو في أجواء مقهى داكنة']],
            'cold-drinks' => ['cold-drinks.webp', ['آیس لاته لایه‌ای روی کانتر', 'Layered iced latte on a café counter', 'آيس لاتيه بطبقات على منضدة المقهى']],
            'fresh-juice' => ['fresh-juice.webp', ['آب انار و پرتقال تازه', 'Fresh pomegranate and orange juices', 'عصيرا الرمان والبرتقال الطازجان']],
            'pastry' => ['pastry.webp', ['کروسان و کیک شکلاتی', 'Croissant and dark chocolate cake', 'كرواسون وكعكة شوكولاتة داكنة']],
            'latte' => ['latte.webp', ['قهوه با شیر و فوم', 'Coffee with milk and foam', 'قهوة بالحليب والرغوة']],
            'tea' => ['tea.webp', ['قوری چای و نعنا', 'Tea pot with mint', 'إبريق شاي ونعناع']],
            'avocado' => ['avocado.webp', ['تست آووکادو', 'Avocado toast', 'توست الأفوكادو']],
            'granola' => ['granola.webp', ['گرانولا و ماست', 'Granola and yoghurt', 'غرانولا ولبن']],
        ];
        $records = [];

        foreach ($assets as $key => [$filename, $alts]) {
            $source = public_path("demo/denardi/{$filename}");
            $path = "demo/denardi/{$filename}";
            Storage::disk('public')->put($path, (string) file_get_contents($source));
            $media = Media::query()->firstOrCreate(
                ['disk' => 'public', 'path' => $path, 'business_id' => $business->id],
                [
                    'public_id' => (string) Str::ulid(),
                    'business_id' => $business->id,
                    'optimized_path' => $path,
                    'thumbnail_path' => $path,
                    'mime' => 'image/webp',
                    'size' => (int) filesize($source),
                    'width' => 1280,
                    'height' => 853,
                    'checksum' => hash_file('sha256', $source),
                    'status' => MediaStatus::Ready,
                    'uploaded_by' => $actor->id,
                ],
            );
            foreach (['fa', 'en', 'ar'] as $index => $locale) {
                $media->translations()->updateOrCreate(['locale' => $locale], ['alt' => $alts[$index], 'title' => null, 'caption' => null]);
            }
            if ($media->wasRecentlyCreated) {
                $media->update(app(MediaDerivativeGenerator::class)->execute('public', $path));
                $this->demoRecords['media'][] = $media->public_id;
            }
            $records[$key] = $media;
        }

        return $records;
    }

    private function saveManifest(Business $business): void
    {
        Storage::disk('local')->put('demo/denardi-'.$business->id.'.json', json_encode($this->demoRecords, JSON_THROW_ON_ERROR));
    }

    /**
     * @param  array{string, string, string}  $names
     * @param  array{string, string, string}  $descriptions
     * @return array<string, array<string, string>>
     */
    private function menuTranslations(array $names, array $descriptions): array
    {
        return [
            'fa' => ['name' => $names[0], 'description' => $descriptions[0], 'translation_state' => 'ready'],
            'en' => ['name' => $names[1], 'description' => $descriptions[1], 'translation_state' => 'ready'],
            'ar' => ['name' => $names[2], 'description' => $descriptions[2], 'translation_state' => 'ready'],
        ];
    }
}
