<?php

namespace App\Http\Middleware;

use App\Services\CurrentUserService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function __construct(
        protected CurrentUserService $currentUserService,
    ) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$this->currentUserService->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
