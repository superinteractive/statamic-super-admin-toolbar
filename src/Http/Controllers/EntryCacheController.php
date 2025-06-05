<?php

declare(strict_types=1);

namespace SuperInteractive\SuperAdminToolbar\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Statamic\Http\Controllers\Controller;
use Statamic\Support\Str;
use SuperInteractive\SuperAdminToolbar\Helpers\ToolbarHelpers;

final class EntryCacheController extends Controller
{
    public function refresh(Request $request)
    {
        // Check if user is authorized
        $user = auth(config('statamic.users.guards.cp', 'web'))->user();

        if (!ToolbarHelpers::userIsAuthorized($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if full measure static caching is enabled
        if (!ToolbarHelpers::isFullMeasureStaticCachingEnabled()) {
            return redirect()->back()->with('error', 'Full measure static caching is not enabled.');
        }

        $path = $request->get('path', '/');

        // Ensure we're only processing a single path
        if (strpos($path, PHP_EOL) !== false) {
            $path = explode(PHP_EOL, $path)[0];
        }

        $this->delete(trim($path));

        return redirect()->back()->with('success', 'Entry cache refreshed successfully.');
    }

    protected function delete($path): void
    {
        $cachePaths = config('statamic.static_caching.strategies.full.path');

        collect(Arr::wrap($cachePaths))->each(function (string $cachePath) use ($path) {
            $fullPath = $cachePath.Str::ensureLeft($path, '/');

            // For root path, only delete the specific file, not the directory
            if ($path === '/') {
                // Skip directory deletion for root path
                $this->deleteFile($fullPath);
            } else {
                if (File::isDirectory($fullPath)) {
                    $this->deleteDirectory($fullPath);
                }

                $this->deleteFile($fullPath);
            }
        });
    }

    protected function deleteFile($path): void
    {
        if (! Str::of($path)->contains('*')) {
            // For root path, specifically target the _.html file
            if ($path === config('statamic.static_caching.strategies.full.path') ||
                Str::endsWith($path, '/')) {
                $path .= '_.html';
            } else {
                $path .= '_*';
            }
        }

        foreach (File::glob($path) as $file) {
            File::delete($file);
        }
    }

    protected function deleteDirectory($path): void
    {
        File::deleteDirectory($path);
    }

}
