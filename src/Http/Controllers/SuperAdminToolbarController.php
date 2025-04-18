<?php

declare(strict_types=1);

namespace SuperInteractive\SuperAdminToolbar\Http\Controllers;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Statamic\Http\Controllers\Controller;
use SuperInteractive\SuperAdminToolbar\Services\ManifestService;
use SuperInteractive\SuperAdminToolbar\Services\ToolbarContextService;
use Throwable;

final class SuperAdminToolbarController extends Controller
{
    private const JS_ENTRY_KEY = 'resources/js/toolbar.js';
    private const CSS_ENTRY_KEY = 'resources/css/toolbar.css';
    private const VIEW_NAME = 'super-admin-toolbar::toolbar';

    public function __invoke(Request $request, ToolbarContextService $contextService, ManifestService $manifestService): JsonResponse
    {
        /** @var Authorizable|null $user */
        $user = auth(config('statamic.users.guards.cp', 'web'))->user();

        if (!$this->userIsAuthorized($user)) {
            return response()->json(['authenticated' => false]);
        }

        $currentUrl = (string)$request->input('url', '');

        if ($currentUrl === '') {
            Log::warning('SuperAdminToolbar: Invalid or missing URL.', ['url' => $currentUrl]);

            return response()->json(['error' => 'Invalid or missing URL.'], 400);
        }

        $payload = $this->buildPayload($currentUrl, $user, $contextService, $manifestService);

        return $payload
            ? response()->json($payload)
            : response()->json(['error' => 'Toolbar processing error.'], 500);
    }

    private function userIsAuthorized(?Authorizable $user): bool
    {
        if (!$user) {
            return false;
        }

        $ability = (string) config('super-admin-toolbar.permission', 'access cp');

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

    private function buildPayload(string $url, Authorizable $user, ToolbarContextService $contextService, ManifestService $manifestService): ?array
    {
        $assets = $manifestService->getJsAndCssUrls(self::JS_ENTRY_KEY, self::CSS_ENTRY_KEY);

        $html = $this->renderHtml($url, $user, $contextService);

        if ($html === null) {
            return null;
        }

        return [
            'html' => $html,
            'css' => $assets['css'],
            'js' => $assets['js'],
            'authenticated' => true,
        ];
    }

    private function renderHtml(string $url, Authorizable $user, ToolbarContextService $contextService): ?string
    {
        try {
            $context = $contextService->buildContextData($url, $user);

            return View::make(self::VIEW_NAME, $context)->render();
        } catch (Throwable $e) {
            Log::error('SuperAdminToolbar: View rendering failed.', [
                'view' => self::VIEW_NAME,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
