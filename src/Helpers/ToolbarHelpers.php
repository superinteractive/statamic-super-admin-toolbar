<?php

namespace SuperInteractive\SuperAdminToolbar\Helpers;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Gate;

class ToolbarHelpers
{
    public static function isFullMeasureStaticCachingEnabled(): bool
    {
        return config('statamic.static_caching.strategy') === 'full'
            && !empty(config('statamic.static_caching.strategies.full.path'));
    }

    public static function userIsAuthorized(?Authorizable $user): bool
    {
        if (!$user) {
            return false;
        }

        $ability = (string)config('super-admin-toolbar.permission', 'access cp');

        if ($ability && Gate::forUser($user)->allows($ability)) {
            return true;
        }

        if (method_exists($user, 'isSuper') && $user->isSuper()) {
            return true;
        }

        if (method_exists($user, 'hasPermission') && $user->hasPermission($ability)) {
            return true;
        }

        return false;
    }
}
