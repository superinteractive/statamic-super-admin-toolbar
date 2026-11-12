<?php

declare(strict_types=1);

namespace SuperInteractive\SuperAdminToolbar\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\StaticCaching\Cacher;
use Statamic\Http\Controllers\Controller;
use SuperInteractive\SuperAdminToolbar\Helpers\ToolbarHelpers;

final class EntryCacheController extends Controller
{
    public function refresh(Request $request, Cacher $cacher)
    {
        $user = auth(config('statamic.users.guards.cp', 'web'))->user();

        if (!ToolbarHelpers::userIsAuthorized($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!ToolbarHelpers::isFullMeasureStaticCachingEnabled()) {
            return redirect()->back()->with('error', 'Full measure static caching is not enabled.');
        }

        $path = $request->validate(['path' => ['required', 'string']])['path'];

        // Statamic only deletes pages it cached itself, so the path can't reach outside the cache.
        $cacher->invalidateUrls([$request->root().$path]);

        return redirect()->back()->with('success', 'Entry cache refreshed successfully.');
    }
}
