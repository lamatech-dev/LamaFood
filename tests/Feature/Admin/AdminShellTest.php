<?php

namespace Tests\Feature\Admin;

use App\Core\Authorization\FoundationRole;
use App\Core\Authorization\ProvisionFoundationRbac;
use App\Core\Business\Actions\CreateBusiness;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminShellTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_shells_are_noindex_and_build_without_exposing_credentials(): void
    {
        $this->withoutVite();

        $this->get('/admin/login')->assertOk()->assertSee('noindex,nofollow')->assertDontSee('LAMATECH_GODFATHER_PASSWORD');
        $this->get('/admin')->assertOk()->assertSee('noindex,nofollow')->assertDontSee('godfather@instance.invalid');
    }

    public function test_business_context_is_locale_driven_and_excludes_instance_credentials(): void
    {
        $business = app(CreateBusiness::class)->execute('Denardi', 'denardi', 'Main', 'main');
        $user = User::factory()->for($business)->create();
        app(ProvisionFoundationRbac::class)->execute($business);
        app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);
        $user->assignRole(FoundationRole::BusinessOwner->value);
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/admin/v1/business/context')
            ->assertOk()
            ->assertJsonPath('data.business.name', 'Denardi')
            ->assertJsonPath('data.locales.0.locale', 'fa')
            ->assertJsonFragment(['locale' => 'ar', 'direction' => 'rtl'])
            ->assertJsonFragment(['locale' => 'en', 'direction' => 'ltr'])
            ->assertJsonMissing(['username' => 'godfather']);
    }
}
