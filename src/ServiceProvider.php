<?php

namespace SuperInteractive\SuperAdminToolbar;

use Illuminate\Support\Facades\Blade;
use Statamic\Providers\AddonServiceProvider;
use SuperInteractive\SuperAdminToolbar\View\Components\Icon;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
        ],
        'publicDirectory' => 'dist',
    ];

    protected $routes = [
        'actions' => __DIR__ . '/../routes/web.php',
    ];

    public function bootAddon()
    {
        parent::bootAddon();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'super-admin-toolbar');

        Blade::component(Icon::class, 'sat-icon');
    }
}
