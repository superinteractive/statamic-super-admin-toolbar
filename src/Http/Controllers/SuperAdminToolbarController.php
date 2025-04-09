<?php

declare(strict_types=1);

namespace SuperInteractive\SuperAdminToolbar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\User;
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
        $user = User::current();

        if (!$this->isUserAuthorized($user)) {
            return response()->json(['authenticated' => false]);
        }

        $currentUrl = $request->input('url');

        if (!is_string($currentUrl) || $currentUrl === '') {
            Log::warning('SuperAdminToolbar: Invalid or missing URL.', ['url' => $currentUrl]);
            return response()->json(['error' => 'Invalid or missing URL.'], 400);
        }

        $payload = $this->preparePayload($currentUrl, $user, $contextService, $manifestService);

        return $payload ? response()->json($payload) : response()->json(['error' => 'Toolbar processing error.'], 500);
    }

    private function isUserAuthorized(?UserContract $user): bool
    {
        return $user && ($user->isSuper() || $user->hasPermission('access cp'));
    }

    private function preparePayload(string $url, UserContract $user, ToolbarContextService $contextService, ManifestService $manifestService): ?array
    {
        $assets = $manifestService->getJsAndCssUrls(self::JS_ENTRY_KEY, self::CSS_ENTRY_KEY);

        $html = $this->renderHtml($url, $user, $contextService);

        return $html !== null ? [
            'html' => $html,
            'css' => $assets['css'],
            'js' => $assets['js'],
            'authenticated' => true,
        ] : null;
    }

    private function renderHtml(string $url, UserContract $user, ToolbarContextService $contextService): ?string
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
