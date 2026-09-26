<?php

namespace App\Http\Middleware;

use App\Enums\SpacePermission;
use App\Models\Space;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use ValueError;

class CheckSpacePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        try {
            $permissionEnum = SpacePermission::from($permission);
        } catch (ValueError $e) {
            abort(500, "Unknown space permission: {$permission}");
        }

        $space = $this->resolveSpace($request);

        if (!$space) {
            abort(404);
        }

        $userId = Auth::id();

        if (!$userId || !$space->isMember($userId)) {
            abort(403);
        }

        if (!$space->userHasPermission($userId, $permissionEnum)) {
            abort(403);
        }

        return $next($request);
    }

    protected function resolveSpace(Request $request): ?Space
    {
        $space = $request->route('space');
        if ($space instanceof Space) {
            return $space;
        }

        $channel = $request->route('channel');
        if ($channel && method_exists($channel, 'space')) {
            return $channel->space;
        }

        return null;
    }
}
