<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Routing\Route;
use Tests\TestCase;

class ReleaseSecurityHeadersTest extends TestCase
{
    public function test_public_admin_and_api_responses_receive_baseline_security_headers(): void
    {
        foreach (['/offline', '/admin/login', '/api/admin/v1/me'] as $path) {
            $response = $this->get($path, ['Accept' => 'application/json']);

            $response->assertHeader('Content-Security-Policy', "base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'")
                ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('X-Frame-Options', 'DENY')
                ->assertHeaderMissing('Strict-Transport-Security');
        }
    }

    public function test_hsts_is_only_sent_for_secure_production_requests(): void
    {
        $this->app->instance('env', 'production');

        $this->get('https://localhost/offline')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_same_origin_api_login_rejects_a_missing_csrf_token(): void
    {
        $this->app->instance('env', 'local');

        $this->withMiddleware(ValidateCsrfToken::class)
            ->withHeader('Origin', 'http://localhost')
            ->postJson('/api/admin/v1/login', [
                'identifier' => 'owner@example.com',
                'password' => 'not-relevant',
                'device_name' => 'release-security-test',
            ])->assertStatus(419);
    }

    public function test_every_private_admin_api_route_has_authentication_and_permission_boundaries(): void
    {
        $publicRouteNames = [
            'api.admin.v1.auth.login',
            'api.admin.v1.auth.password.email',
            'api.admin.v1.auth.password.update',
        ];
        $permissionExceptions = [
            'api.admin.v1.auth.logout',
            'api.admin.v1.auth.me',
        ];
        $privateRoutes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => str_starts_with($route->uri(), 'api/admin/v1/'))
            ->reject(fn (Route $route): bool => in_array($route->getName(), $publicRouteNames, true));

        $this->assertGreaterThan(30, $privateRoutes->count());

        $privateRoutes->each(function (Route $route) use ($permissionExceptions): void {
            $middleware = $route->gatherMiddleware();
            $this->assertContains('auth:sanctum', $middleware, "{$route->uri()} must require authentication.");

            if (! in_array($route->getName(), $permissionExceptions, true)) {
                $this->assertNotEmpty(
                    array_filter($middleware, fn (string $name): bool => str_starts_with($name, 'permission:')),
                    "{$route->uri()} must require an explicit permission.",
                );
            }
        });
    }
}
