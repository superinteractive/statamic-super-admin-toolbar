<?php

namespace SuperInteractive\SuperAdminToolbar\Services;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Facades\Addon;
use Statamic\Facades\Site as SiteAPI;
use Statamic\Sites\Site as ConcreteSite;
use Statamic\Taxonomies\LocalizedTerm;
use SuperInteractive\SuperAdminToolbar\Helpers\ToolbarHelpers;
use Throwable;

class ToolbarContextService
{
    public static function getPageJsonData($site, $page): string
    {
        $data = [
            'siteHandle' => $site->handle,
            'pageType' => null,
            'currentPath' => request()->path(),
        ];

        if (!empty($page)) {
            if ($page instanceof LocalizedTerm) {
                $data['pageType'] = 'term';
                $data['term'] = $page->term()->id();
            } else {
                $data['pageType'] = 'entry';
                $data['entry'] = $page->id;
            }
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public function buildContextData($site, $model): array
    {
        /* @var Authorizable */
        $user = auth(config('statamic.users.guards.cp', 'web'))->user();

        $context = [
            'editUrl' => null,
            'createUrl' => null,
            'seoUrl' => null,
            'currentModel' => null,
            'toolbarOpened' => $this->isToolbarOpened(),
            'multiSites' => null,
            'currentSiteHandle' => null,
            'isFullMeasureStaticCachingEnabled' => ToolbarHelpers::isFullMeasureStaticCachingEnabled(),
        ];

        if (!$site) {
            return $context;
        }

        $context['multiSites'] = $this->getMultiSitesData();

        $context['currentModel'] = $model;
        $context['editUrl'] = $this->generateEditUrl($model, $user);
        $context['createUrl'] = $this->generateCreateUrl($model, $user);
        $context['seoUrl'] = $this->generateSeoUrl($model, $context['editUrl']);

        return $context;
    }

    private function generateEditUrl(EntryContract|TermContract|null $model, Authorizable $user): ?string
    {
        if ($model && method_exists($model, 'editUrl') && $user->can('edit', $model)) {
            return $model->editUrl();
        }

        return null;
    }

    private function generateSeoUrl(EntryContract|TermContract|null $model, ?string $editUrl): ?string
    {
        if (!$model || !$editUrl) {
            return null;
        }

        try {
            $seoProAddon = Addon::get('statamic/seo-pro');
        } catch (Throwable $e) {
            Log::error("ToolbarContextService: Error checking for SEO Pro addon.", ['exception' => $e]);
            return null;
        }

        return $seoProAddon ? $editUrl . '#seo' : null;
    }

    private function generateCreateUrl(EntryContract|TermContract|null $model, Authorizable $user): ?string
    {
        if ($model instanceof EntryContract) {
            $collection = $model->collection();
            $canCreateInCollection = $user->can('create', [EntryContract::class, $collection]) && $user->can('create', [$collection::class, $collection]);

            if ($canCreateInCollection) {
                return cp_route('collections.entries.create', [
                    'collection' => $collection->handle(),
                    'site' => $model->site()->handle(),
                ]);
            }
        }

        return null;
    }

    private function isToolbarOpened(): bool
    {
        return Arr::get($_COOKIE, 'super-admin-toolbar-opened', 'false') === 'true';
    }

    private function getMultiSitesData(): ?array
    {
        try {
            $sites = SiteAPI::all();

            if ($sites->count() <= 1) {
                return null;
            }

            return $sites->map(fn(ConcreteSite $site) => [
                'name' => $site->name(),
                'url' => $site->absoluteUrl(),
                'handle' => $site->handle(),
            ])->sortBy('name')->values()->all();

        } catch (Throwable $e) {
            Log::error('ToolbarContextService: Error fetching multisite data.', ['exception' => $e]);
            return null;
        }
    }

}
