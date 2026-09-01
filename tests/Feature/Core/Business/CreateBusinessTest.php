<?php

namespace Tests\Feature\Core\Business;

use App\Core\Business\Actions\CreateBusiness;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CreateBusinessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_business_default_branch_and_all_required_denardi_locales_atomically(): void
    {
        $business = app(CreateBusiness::class)->execute(
            name: 'Denardi',
            slug: 'denardi',
            branchName: 'Main',
            branchSlug: 'main',
        );

        $this->assertSame('fa', $business->default_locale);
        $this->assertSame(1, $business->branches->count());
        $this->assertTrue($business->branches->firstOrFail()->is_default);
        $this->assertSame(['ar', 'en', 'fa'], $business->locales->pluck('locale')->sort()->values()->all());
        $this->assertSame(['rtl', 'ltr', 'rtl'], $business->locales->sortBy('id')->pluck('direction')->map->value->all());
        $this->assertTrue($business->locales->every('is_required_for_publication', true));
    }
}
