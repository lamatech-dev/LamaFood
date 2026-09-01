<?php

namespace Tests\Feature\Api\Admin\V1\Qr;

use App\Core\Authorization\FoundationRole;
use App\Core\Authorization\ProvisionFoundationRbac;
use App\Core\Business\Models\Branch;
use App\Core\Business\Models\Business;
use App\Core\Qr\Models\QrCode;
use App\Core\Qr\QrCodeType;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class QrCodeArtworkControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_401_when_unauthenticated(): void
    {
        $branch = Branch::factory()->create();
        $qrCode = QrCode::factory()->for($branch)->create(['business_id' => $branch->business_id]);

        $this->getJson("/api/admin/v1/qr-codes/{$qrCode->public_id}/artwork/svg")
            ->assertUnauthorized();
    }

    public function test_godfather_can_download_real_svg_png_and_pdf_artwork(): void
    {
        $branch = Branch::factory()->create();
        $qrCode = QrCode::factory()->for($branch)->create([
            'business_id' => $branch->business_id,
            'type' => QrCodeType::Table,
            'table_key' => 'table-12',
        ]);
        Sanctum::actingAs(User::factory()->godfather()->create());

        $svg = $this->get("/api/admin/v1/qr-codes/{$qrCode->public_id}/artwork/svg");
        $png = $this->get("/api/admin/v1/qr-codes/{$qrCode->public_id}/artwork/png");
        $pdf = $this->get("/api/admin/v1/qr-codes/{$qrCode->public_id}/artwork/pdf");

        $svg->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('<svg', (string) $svg->getContent());
        $this->assertSame(
            "attachment; filename=\"qr-table-{$qrCode->public_id}.svg\"",
            $svg->headers->get('Content-Disposition'),
        );

        $png->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", (string) $png->getContent());

        $pdf->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', (string) $pdf->getContent());
        $this->assertGreaterThan(10_000, mb_strlen((string) $pdf->getContent(), '8bit'));
    }

    public function test_business_user_cannot_discover_another_business_qr_artwork(): void
    {
        $ownBusiness = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        $otherBranch = Branch::factory()->for($otherBusiness)->create();
        $qrCode = QrCode::factory()->for($otherBranch)->create(['business_id' => $otherBusiness->id]);
        $user = User::factory()->for($ownBusiness)->create();
        app(ProvisionFoundationRbac::class)->execute($ownBusiness);
        app(PermissionRegistrar::class)->setPermissionsTeamId($ownBusiness->id);
        $user->assignRole(FoundationRole::BusinessOwner->value);
        Sanctum::actingAs($user, ['*']);

        $this->getJson("/api/admin/v1/qr-codes/{$qrCode->public_id}/artwork/svg")
            ->assertNotFound();
    }

    public function test_rejects_formats_outside_the_v1_artwork_allowlist(): void
    {
        $branch = Branch::factory()->create();
        $qrCode = QrCode::factory()->for($branch)->create(['business_id' => $branch->business_id]);
        Sanctum::actingAs(User::factory()->godfather()->create());

        $this->getJson("/api/admin/v1/qr-codes/{$qrCode->public_id}/artwork/campaign")
            ->assertNotFound();
    }
}
