<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class SetBusinessPermissionContext
{
    public function __construct(private readonly PermissionRegistrar $permissions) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $this->permissions->setPermissionsTeamId($request->user()?->business_id);

        return $next($request);
    }
}
