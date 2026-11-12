<?php

use Statamic\Events\EntryBlueprintFound;
use Statamic\Facades\Addon;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use SuperInteractive\SuperAdminToolbar\Services\ToolbarContextService;

it('serializes pages without a saved entry or term as generic context', function ($page) {
    expect(json_decode(ToolbarContextService::getPageJsonData(Site::get('nl'), $page), true))->toBe([
        'siteHandle' => 'nl',
        'pageType' => 'generic',
        'currentPath' => '/',
    ]);
})->with([
    'no page' => [null],
    'unsupported page' => [fn () => (object) ['id' => 'other-content']],
    'unsaved entry' => [fn () => Entry::make()],
]);

it('exposes entry actions only to permitted users', function () {
    $entry = createLocalizedEntry();
    $service = app(ToolbarContextService::class);

    $this->actingAs(User::make()->makeSuper());
    expect($service->buildContextData(Site::get('nl'), $entry))->toMatchArray([
        'editUrl' => $entry->editUrl(),
        'createUrl' => cp_route('collections.entries.create', ['collection' => 'pages', 'site' => 'nl']),
        'currentSiteHandle' => 'nl',
    ]);

    $this->actingAs(User::make()->id('editor'));
    expect($service->buildContextData(Site::get('nl'), $entry))->toMatchArray([
        'editUrl' => null,
        'createUrl' => null,
    ]);
});

it('exposes term actions only to permitted users', function () {
    $term = createLocalizedTerm();
    $service = app(ToolbarContextService::class);

    $this->actingAs(User::make()->makeSuper());
    expect($service->buildContextData(Site::get('nl'), $term))->toMatchArray([
        'editUrl' => $term->editUrl(),
        'createUrl' => null,
    ]);

    $this->actingAs(User::make()->id('editor'));
    expect($service->buildContextData(Site::get('nl'), $term)['editUrl'])->toBeNull();
});

it('links Edit SEO to the tab holding the SEO Pro field', function (?string $tab, bool $seoProInstalled, ?string $fragment) {
    if ($seoProInstalled) {
        Addon::partialMock()->shouldReceive('get')->with('statamic/seo-pro')->andReturn(new stdClass());
    }

    if ($tab) {
        Event::listen(EntryBlueprintFound::class, fn (EntryBlueprintFound $event) => $event->blueprint
            ->ensureFieldInTab('seo', ['type' => 'seo_pro'], $tab));
    }

    $entry = createLocalizedEntry();
    $this->actingAs(User::make()->makeSuper());

    expect(app(ToolbarContextService::class)->buildContextData(Site::get('nl'), $entry)['seoUrl'])
        ->toBe($fragment ? $entry->editUrl().$fragment : null);
})->with([
    'custom tab' => ['metadata', true, '#metadata'],
    'SEO Pro default tab' => ['SEO Meta', true, null],
    'no SEO field' => [null, true, null],
    'SEO Pro not installed' => ['metadata', false, null],
]);
