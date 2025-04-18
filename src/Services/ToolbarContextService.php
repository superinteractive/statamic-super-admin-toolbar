<?php

declare(strict_types=1);

namespace SuperInteractive\SuperAdminToolbar\Services;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Facades\Addon;
use Statamic\Facades\Entry as EntryAPI;
use Statamic\Facades\Site as SiteAPI;
use Statamic\Facades\Term as TermAPI;
use Statamic\Facades\URL as StatamicURL;
use Statamic\Sites\Site as ConcreteSite;
use Statamic\Structures\Page;
use Throwable;

final class ToolbarContextService
{
    public function buildContextData(string $url, Authorizable $user): array
    {
        $context = [
            'editUrl' => null,
            'createUrl' => null,
            'seoUrl' => null,
            'currentModel' => null,
            'toolbarOpened' => $this->isToolbarOpened(),
            'itemSingularName' => __('Item'),
            'multiSites' => null,
            'currentSiteHandle' => null,
        ];

        $site = $this->findSiteForUrl($url);

        if (!$site) {
            return $context;
        }

        $context['currentSiteHandle'] = $site->handle();
        $context['multiSites'] = $this->getMultiSitesData();

        $processedUrl = $this->processUrl($url);

        if (!$processedUrl) {
            return $context;
        }

        $model = $this->findContextualModel($processedUrl['uri'], $processedUrl['absolute'], $site);
        $context['currentModel'] = $model;
        $context['itemSingularName'] = $this->getItemSingularName($model);
        $context['editUrl'] = $this->generateEditUrl($model, $user);
        $context['createUrl'] = $this->generateCreateUrl($model, $user);
        $context['seoUrl'] = $this->generateSeoUrl($model, $context['editUrl']);

        return $context;
    }

    private function getItemSingularName(EntryContract|TermContract|null $model): string
    {
        return match (true) {
            $model instanceof EntryContract => Str::singular($model->collection()->title()),
            $model instanceof TermContract => Str::singular($model->taxonomy()->title()),
            default => __('Item'),
        };
    }

    private function processUrl(string $url): ?array
    {
        try {
            $absolute = StatamicURL::makeAbsolute($url);
            $relative = StatamicURL::makeRelative($absolute);
            $uri = ($relative === '/' || $relative === '') ? '/' : trim($relative, '/');

            return compact('absolute', 'relative', 'uri');
        } catch (Throwable $e) {
            Log::error('ToolbarContextService: Failed processing URL.', ['url' => $url, 'exception' => $e]);

            return null;
        }
    }

    private function findSiteForUrl(string $url): ?ConcreteSite
    {
        try {
            $absoluteUrl = rtrim(StatamicURL::makeAbsolute($url), '/');

            $matchedSite = SiteAPI::all()
                ->sortByDesc(fn($site) => strlen(rtrim($site->absoluteUrl(), '/')))
                ->first(function (ConcreteSite $site) use ($absoluteUrl) {
                    $siteUrl = rtrim($site->absoluteUrl(), '/');
                    return Str::startsWith($absoluteUrl, $siteUrl);
                });

            if (!$matchedSite) {
                Log::warning('ToolbarContextService: Could not determine site for URL.', ['url' => $url]);
            }

            return $matchedSite;
        } catch (Throwable $e) {
            Log::error('ToolbarContextService: Error finding site for URL.', ['exception' => $e, 'url' => $url]);
            return null;
        }
    }

    private function findContextualModel(string $uri, string $absoluteUrl, ConcreteSite $site): EntryContract|TermContract|null
    {
        $siteHandle = $site->handle();
        $model = null;

        try {
            $model = $this->findEntryByUri($uri, $siteHandle);

            if (!$model) {
                $model = $this->findEntryByAbsoluteUrl($absoluteUrl, $siteHandle);
            }

            if (!$model) {
                $model = $this->findTermByUri($uri, $siteHandle);
            }

            if ($uri === '/' && !$model) {
                $model = $this->findHomeEntry($site);
            }

        } catch (Throwable $e) {
            Log::error(
                'ToolbarContextService: Error finding contextual model.',
                ['exception' => $e, 'uri' => $uri, 'site' => $siteHandle]
            );
            $model = null;
        }

        return $model;
    }

    private function findEntryByUri(string $uri, string $siteHandle): ?EntryContract
    {
        $entry = EntryAPI::findByUri($uri, $siteHandle);

        if ($entry instanceof Page) {
            $entry = $entry->entry();
        }

        if ($entry instanceof EntryContract) {
            $entryUriNormalized = ($entry->uri() === '/' || $entry->uri() === '') ? '/' : trim($entry->uri(), '/');
            if ($entryUriNormalized === $uri) {
                return $entry;
            }
        }

        return null;
    }

    private function findEntryByAbsoluteUrl(string $absoluteUrl, string $siteHandle): ?EntryContract
    {
        $entry = EntryAPI::query()
            ->where('site', $siteHandle)
            ->get()
            ->first(function (EntryContract $entry) use ($absoluteUrl) {
                $entryUrl = $entry->url();

                if (!$entryUrl) {
                    return false;
                }

                // Ensure consistent trailing slash handling
                $entryAbsoluteUrl = rtrim(StatamicURL::makeAbsolute($entryUrl), '/');
                $targetAbsoluteUrl = rtrim($absoluteUrl, '/');

                return $entryAbsoluteUrl === $targetAbsoluteUrl;
            });


        if ($entry instanceof Page) {
            $entry = $entry->entry();
        }

        return $entry instanceof EntryContract ? $entry : null;
    }

    private function findTermByUri(string $uri, string $siteHandle): ?TermContract
    {
        $term = TermAPI::findByUri($uri, $siteHandle);

        return $term instanceof TermContract ? $term : null;
    }

    private function findHomeEntry(ConcreteSite $site): ?EntryContract
    {
        $entry = EntryAPI::findByUri('/', $site->handle());

        if ($entry instanceof Page) {
            $entry = $entry->entry();
        }

        return $entry instanceof EntryContract ? $entry : null;
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
            $canCreateInCollection = $user->can('create', EntryContract::class) && $user->can('create', [$collection::class, $collection]);

            if ($canCreateInCollection) {
                return cp_route('collections.entries.create', [
                    'collection' => $collection->handle(),
                    'site' => $model->site()->handle(),
                ]);
            }
        }

        if ($user->can('create', EntryContract::class)) {
            return cp_route('collections.index');
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
