<?php

use Illuminate\Support\Facades\File;
use Statamic\Facades\User;

beforeEach(function () {
    config([
        'statamic.static_caching.strategy' => 'full',
        'statamic.static_caching.strategies.full.path' => public_path('static'),
    ]);

    foreach (['static/nl/page_.html', 'static/nl/page_a=1.html', 'static/nl/page/child_.html', 'secret/keep_.html'] as $file) {
        File::ensureDirectoryExists(dirname(public_path($file)));
        File::put(public_path($file), 'cached');
    }
});

it('refreshes only the requested page and its query variants', function () {
    $this->actingAs(User::make()->makeSuper());

    $this->post('/super-admin-toolbar/entry-cache/refresh', ['path' => '/nl/page'])->assertRedirect();

    expect(public_path('static/nl/page_.html'))->not->toBeFile()
        ->and(public_path('static/nl/page_a=1.html'))->not->toBeFile()
        ->and(public_path('static/nl/page/child_.html'))->toBeFile();
});

it('never deletes files outside the static cache', function () {
    $this->actingAs(User::make()->makeSuper());

    $this->post('/super-admin-toolbar/entry-cache/refresh', ['path' => '/../secret/keep'])->assertRedirect();

    expect(public_path('secret/keep_.html'))->toBeFile();
});

it('rejects users without toolbar access', function () {
    $this->actingAs(User::make()->id('editor'));

    $this->post('/super-admin-toolbar/entry-cache/refresh', ['path' => '/nl/page'])->assertForbidden();

    expect(public_path('static/nl/page_.html'))->toBeFile();
});
