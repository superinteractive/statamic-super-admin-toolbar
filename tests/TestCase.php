<?php

namespace Tests;

use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Testing\AddonTestCase;
use SuperInteractive\SuperAdminToolbar\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'statamic.editions.pro' => true,
            'statamic.system.multisite' => true,
        ]);

        $this->directory = sys_get_temp_dir().'/super-admin-toolbar-'.Str::uuid();
        $this->app['files']->ensureDirectoryExists($this->directory.'/public/vendor');
        $this->app->usePublicPath($this->directory.'/public');
        symlink(dirname(__DIR__).'/resources/dist', public_path('vendor/statamic-super-admin-toolbar'));

        Site::setSites([
            'default' => ['name' => 'English', 'url' => '/', 'locale' => 'en'],
            'nl' => ['name' => 'Dutch', 'url' => '/nl/', 'locale' => 'nl'],
        ]);

        Stache::sites(['default', 'nl']);
        Stache::stores()->each(fn ($store) => $store->directory($this->directory.'/'.$store->key()));
    }

    protected function tearDown(): void
    {
        $this->app['files']->deleteDirectory($this->directory);

        parent::tearDown();
    }
}
