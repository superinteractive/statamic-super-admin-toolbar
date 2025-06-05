<?php

declare(strict_types=1);

namespace SuperInteractive\SuperAdminToolbar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Http\Controllers\Controller;
use SuperInteractive\SuperAdminToolbar\Services\ManifestService;
use SuperInteractive\SuperAdminToolbar\Services\ToolbarContextService;
use SuperInteractive\SuperAdminToolbar\Helpers\ToolbarHelpers;
use Throwable;

final class SuperAdminToolbarController extends Controller
{
    public function __invoke(Request $request, ToolbarContextService $contextService, ManifestService $manifestService): JsonResponse
    {
        $user = auth(config('statamic.users.guards.cp', 'web'))->user();

        if (!ToolbarHelpers::userIsAuthorized($user)) {
            return response()->json(['authenticated' => false]);
        }

        $siteHandle = $request->input('siteHandle', '');
        $pageType = $request->input('pageType', '');
        $currentPath = $request->input('currentPath', '');

        if (empty($siteHandle)) {
            Log::warning('SuperAdminToolbar: Invalid or missing site handle.', ['siteHandle' => $siteHandle]);

            return response()->json(['error' => 'Invalid or missing site handle.'], 400);
        }

        if (empty($pageType)) {
            Log::warning('SuperAdminToolbar: Invalid or missing page type.', ['pageType' => $pageType]);

            return response()->json(['error' => 'Invalid or missing page type.'], 400);
        }

        $site = Site::get($siteHandle);

        // Initialize $model with null as default
        $model = null;

        if ($pageType === 'entry') {
            $model = Entry::findOrFail($request->input('entry', null));
        }

        if ($pageType === 'term') {
            $model = Term::findOrFail($request->input('term', null));
        }

        $payload = $this->buildPayload($site, $model, $currentPath);

        return $payload
            ? response()->json($payload)
            : response()->json(['error' => 'Toolbar processing error.'], 500);
    }


    private function buildPayload($site, $model, $currentPath): ?array
    {
        $manifestService = new ManifestService();

        $assets = $manifestService->getJsAndCssUrls();

        $html = $this->renderHtml($site, $model, $currentPath);

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

    private function renderHtml($site, $model, $currentPath): ?string
    {
        $contextService = app(ToolbarContextService::class);

        try {
            $context = $contextService->buildContextData($site, $model);
            $context['currentPath'] = $currentPath;

            return View::make('super-admin-toolbar::toolbar', $context)->render();
        } catch (Throwable $e) {
            Log::error('SuperAdminToolbar: View rendering failed.', [
                'view' => 'super-admin-toolbar::toolbar',
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
