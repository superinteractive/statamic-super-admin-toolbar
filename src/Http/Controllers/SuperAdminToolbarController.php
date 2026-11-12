<?php

declare(strict_types=1);

namespace SuperInteractive\SuperAdminToolbar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Http\Controllers\Controller;
use SuperInteractive\SuperAdminToolbar\Services\ManifestService;
use SuperInteractive\SuperAdminToolbar\Services\ToolbarContextService;
use SuperInteractive\SuperAdminToolbar\Helpers\ToolbarHelpers;

final class SuperAdminToolbarController extends Controller
{
    public function __invoke(Request $request, ToolbarContextService $contextService, ManifestService $manifestService): JsonResponse
    {
        $user = auth(config('statamic.users.guards.cp', 'web'))->user();

        if (!ToolbarHelpers::userIsAuthorized($user)) {
            return response()->json(['authenticated' => false]);
        }

        $validated = $request->validate([
            'siteHandle' => ['required', 'string', Rule::in(Site::all()->keys())],
            'pageType' => ['required', Rule::in(['generic', 'entry', 'term'])],
            'currentPath' => ['required', 'string'],
            'entry' => ['nullable', 'string'],
            'term' => ['nullable', 'string'],
        ]);

        $site = Site::get($validated['siteHandle']);
        $id = $validated[$validated['pageType']] ?? null;

        // A missing or deleted entry or term falls back to the generic toolbar.
        $model = match (true) {
            $id === null => null,
            $validated['pageType'] === 'entry' => Entry::find($id),
            default => Term::find($id)?->in($site->handle()),
        };

        $context = $contextService->buildContextData($site, $model);
        $context['currentPath'] = $validated['currentPath'];

        return response()->json([
            'html' => view('super-admin-toolbar::toolbar', $context)->render(),
            ...$manifestService->getJsAndCssUrls(),
            'authenticated' => true,
        ]);
    }
}
