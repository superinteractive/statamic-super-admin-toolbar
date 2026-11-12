<?php

namespace SuperInteractive\SuperAdminToolbar\Services;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Facades\Addon;
use Statamic\Facades\Site as SiteAPI;
use Statamic\Sites\Site;
use Statamic\Taxonomies\LocalizedTerm;
use SuperInteractive\SuperAdminToolbar\Helpers\ToolbarHelpers;

class ToolbarContextService
{
    public static function getPageJsonData($site, $page): string
    {
        $data = [
            'siteHandle' => $site->handle,
            'pageType' => 'generic',
            'currentPath' => request()->path(),
        ];

        if ($page instanceof LocalizedTerm) {
            $data['pageType'] = 'term';
            $data['term'] = $page->id();
        } elseif ($page instanceof EntryContract && $page->id()) {
            $data['pageType'] = 'entry';
            $data['entry'] = $page->id();
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public function buildContextData(Site $site, EntryContract|TermContract|null $model): array
    {
        /* @var Authorizable */
        $user = auth(config('statamic.users.guards.cp', 'web'))->user();

        $editUrl = $model && $user->can('edit', $model) ? $model->editUrl() : null;

        return [
            'editUrl' => $editUrl,
            'createUrl' => $this->generateCreateUrl($model, $user),
            'seoUrl' => $editUrl ? $this->generateSeoUrl($model, $editUrl) : null,
            'toolbarOpened' => ($_COOKIE['super-admin-toolbar-opened'] ?? 'false') === 'true',
            'multiSites' => $this->getMultiSitesData(),
            'currentSiteHandle' => $site->handle(),
            'isFullMeasureStaticCachingEnabled' => ToolbarHelpers::isFullMeasureStaticCachingEnabled(),
        ];
    }

    private function generateSeoUrl(EntryContract|TermContract $model, string $editUrl): ?string
    {
        if (!Addon::get('statamic/seo-pro')) {
            return null;
        }

        $handle = $model->blueprint()->tabs()
            ->first(fn ($tab) => $tab->fields()->get('seo')?->type() === 'seo_pro')
            ?->handle();

        // The CP compares the raw URL fragment with tab handles, so a handle that needs encoding
        // (like SEO Pro's default "SEO Meta") would open the first tab instead.
        return $handle && rawurlencode($handle) === $handle ? "{$editUrl}#{$handle}" : null;
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

    private function getMultiSitesData(): ?array
    {
        $sites = SiteAPI::all();

        if ($sites->count() <= 1) {
            return null;
        }

        return $sites->map(fn (Site $site) => [
            'name' => $site->name(),
            'url' => $site->absoluteUrl(),
            'handle' => $site->handle(),
        ])->sortBy('name')->values()->all();
    }
}
