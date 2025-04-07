<?php

declare(strict_types=1);

namespace SuperInteractive\SuperAdminToolbar\Http\Controllers;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use JsonException;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;
use SuperInteractive\SuperAdminToolbar\Services\ToolbarContextService;
use Throwable;

final class SuperAdminToolbarController extends Controller
{
    private const MANIFEST_RELATIVE_PATH = 'vendor/statamic-super-admin-toolbar/build/manifest.json';
    private const ASSET_BASE_URL = '/vendor/statamic-super-admin-toolbar/build/';
    private const JS_ENTRY_KEY = 'resources/js/toolbar.js';
    private const CSS_ENTRY_KEY = 'resources/css/toolbar.css';
    private const VIEW_NAME = 'super-admin-toolbar::toolbar';

    public function __invoke(Request $request, ToolbarContextService $contextService): JsonResponse
    {
        $user = User::current();

        if (! $this->isUserAuthorized($user)) {
            return response()->json(['authenticated' => false]);
        }

        $currentUrl = $request->input('url');

        if (! is_string($currentUrl) || empty($currentUrl)) {
            Log::warning('SuperAdminToolbar: Invalid or missing URL in request.', ['url' => $currentUrl]);
            return response()->json(['error' => 'Invalid or missing URL provided.'], 400);
        }

        $responsePayload = $this->prepareResponsePayload($currentUrl, $user, $contextService);

        if (is_null($responsePayload)) {
            return response()->json(['error' => 'Toolbar configuration or processing error on server.'], 500);
        }

        return response()->json($responsePayload);
    }

    private function isUserAuthorized(?UserContract $user): bool
    {
        if (! $user) {
            Log::debug('SuperAdminToolbar: Access forbidden (no user).');
            return false;
        }

        if (! $user->isSuper() && ! $user->hasPermission('access cp')) {
            Log::debug('SuperAdminToolbar: Access forbidden (insufficient permissions).', ['userId' => $user->id()]);
            return false;
        }

        return true;
    }

    private function prepareResponsePayload(string $url, UserContract $user, ToolbarContextService $contextService): ?array
    {
        $assets = $this->getManifestAssets();
        if (is_null($assets)) {
            return null;
        }

        $html = $this->renderToolbarHtml($url, $user, $contextService);
        if (is_null($html)) {
            return null;
        }

        return [
            'html' => $html,
            'css' => $assets['css'],
            'js' => $assets['js'],
        ];
    }

    private function getManifestAssets(): ?array
    {
        $manifestPath = public_path(self::MANIFEST_RELATIVE_PATH);

        if (! file_exists($manifestPath)) {
            Log::error('SuperAdminToolbar: Manifest file not found.', ['path' => $manifestPath]);
            return null;
        }

        try {
            $manifestJson = file_get_contents($manifestPath);

            if ($manifestJson === false) {
                throw new \RuntimeException("Could not read manifest file: {$manifestPath}");
            }

            $manifest = json_decode(
                json: $manifestJson,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (JsonException | \RuntimeException $e) {
            Log::error('SuperAdminToolbar: Error reading or decoding manifest JSON.', [
                'path' => $manifestPath,
                'exception' => $e->getMessage(),
            ]);
            return null;
        }

        $jsFile = $manifest[self::JS_ENTRY_KEY]['file'] ?? null;
        $cssFile = $manifest[self::CSS_ENTRY_KEY]['file'] ?? $manifest[self::JS_ENTRY_KEY]['css'][0] ?? null;

        if (! $jsFile) {
            Log::error('SuperAdminToolbar: JS entry file not found in manifest.', ['key' => self::JS_ENTRY_KEY]);
            // Allow proceeding without JS potentially, but log error. CSS is often bundled with JS.
        }

        $baseUrl = url(self::ASSET_BASE_URL) . '/';

        return [
            'css' => $cssFile ? $baseUrl . $cssFile : null,
            'js' => $jsFile ? $baseUrl . $jsFile : null,
        ];
    }

    private function renderToolbarHtml(string $url, UserContract $user, ToolbarContextService $contextService): ?string
    {
        try {
            $contextData = $contextService->buildContextData($url, $user);
            $view = View::make(self::VIEW_NAME, $contextData);

            return $view->render();
        } catch (Throwable $e) {
            Log::error('SuperAdminToolbar: Error rendering toolbar view.', [
                'view' => self::VIEW_NAME,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Consider reducing trace length in production
            ]);

            return null;
        }
    }
}
