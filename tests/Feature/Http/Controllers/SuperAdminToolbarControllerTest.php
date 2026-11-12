<?php

use Illuminate\Support\Facades\Blade;
use Statamic\Facades\Cascade;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use SuperInteractive\SuperAdminToolbar\Services\ToolbarContextService;

it('reports unauthorized users before validating the context', function () {
    $this->actingAs(User::make()->id('editor'));

    $this->getJson('/super-admin-toolbar')->assertExactJson(['authenticated' => false]);
});

it('rejects an invalid context', function (array $context) {
    $this->actingAs(User::make()->makeSuper());

    $this->getJson('/super-admin-toolbar?'.http_build_query($context + [
        'siteHandle' => 'nl',
        'pageType' => 'entry',
        'currentPath' => 'nl/page',
    ]))->assertUnprocessable();
})->with([
    'missing site' => [['siteHandle' => null]],
    'unknown site' => [['siteHandle' => 'unknown']],
    'unknown page type' => [['pageType' => 'unknown']],
    'missing path' => [['currentPath' => null]],
    'entry ID array' => [['entry' => ['page']]],
]);

it('falls back to the generic toolbar when no entry or term resolves', function (array $context) {
    $this->actingAs(User::make()->makeSuper());

    $html = $this->getJson('/super-admin-toolbar?'.http_build_query($context + [
        'siteHandle' => 'nl',
        'currentPath' => 'nl/page',
    ]))->assertOk()->assertJsonPath('authenticated', true)->json('html');

    expect($html)
        ->toContain(cp_route('dashboard'))
        ->not->toContain(cp_route('collections.index'))
        ->not->toContain(cp_route('taxonomies.index'));
})->with([
    'generic page' => [['pageType' => 'generic']],
    'entry without ID' => [['pageType' => 'entry']],
    'v2.1.1 cached entry without ID' => [['pageType' => 'entry', 'entry' => 'null']],
    'term without ID' => [['pageType' => 'term']],
    'deleted term' => [['pageType' => 'term', 'term' => 'topics::missing']],
    'malformed term ID' => [['pageType' => 'term', 'term' => 'missing']],
]);

it('loads the toolbar on a cached page after its entry is deleted', function () {
    $entry = createLocalizedEntry();
    Cascade::data(['site' => Site::get('nl'), 'page' => $entry]);
    $cachedPage = new DOMDocument();
    $cachedPage->loadHTML(Blade::render('@superAdminToolbar'));
    $url = '/super-admin-toolbar?'.http_build_query(json_decode($cachedPage->getElementById('super-admin-toolbar-context-json')->textContent, true));
    $this->actingAs(User::make()->makeSuper());

    expect($this->getJson($url)->json('html'))->toContain($entry->editUrl());

    $entry->delete();

    expect($this->getJson($url)->assertOk()->json('html'))
        ->toContain(cp_route('dashboard'))
        ->not->toContain($entry->editUrl());
});

it('resolves a term in the requested site', function () {
    $term = createLocalizedTerm();
    $this->actingAs(User::make()->makeSuper());
    $context = json_decode(ToolbarContextService::getPageJsonData(Site::get('nl'), $term), true);

    expect($this->getJson('/super-admin-toolbar?'.http_build_query($context))->json('html'))
        ->toContain($term->editUrl())
        ->not->toContain($term->inDefaultLocale()->editUrl());
});
