<?php

namespace SuperInteractive\SuperAdminToolbar;

use Illuminate\Support\Facades\Blade;
use Statamic\Providers\AddonServiceProvider;
use SuperInteractive\SuperAdminToolbar\Tags\SuperAdminToolbar as SuperAdminToolbarTag;
use SuperInteractive\SuperAdminToolbar\View\Components\Icon;

class ServiceProvider extends AddonServiceProvider
{
    public static string $handle = 'superinteractive/statamic-super-admin-toolbar';

    protected $vite = [
        'input' => [
            'resources/js/toolbar.js',
            'resources/css/toolbar.css',
        ],
        'publicDirectory' => 'resources/dist',
        'hotFile' => 'resources/dist/hot',
    ];

    protected $routes = [
        'actions' => __DIR__ . '/../routes/web.php',
    ];

    protected $tags = [
        SuperAdminToolbarTag::class,
    ];

    public function bootAddon()
    {
        parent::bootAddon();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'super-admin-toolbar');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'super-admin-toolbar');
        Blade::component(Icon::class, 'sat-icon');

        Blade::directive('superAdminToolbar', function () {
            return "<?php echo view('super-admin-toolbar::load-toolbar')->render(); ?>";
        });
    }
}
