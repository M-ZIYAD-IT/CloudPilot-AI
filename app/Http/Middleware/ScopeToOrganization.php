<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeToOrganization
{
    /**
     * Resolve the authenticated user's organization and bind it into the
     * container as the current tenant for the rest of the request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organization = $request->user()?->organizations()->first();

        abort_if($organization === null, 403, 'Your account is not attached to an organization.');

        app()->instance(Organization::class, $organization);

        return $next($request);
    }
}
