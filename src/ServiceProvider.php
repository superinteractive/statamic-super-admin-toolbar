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
        'publicDirectory' => 'resources/dist',
        'hotFile' => __DIR__.'/../resources/dist/hot',
    ];

    protected $routes = [
        'actions' => __DIR__ . '/../routes/web.php',
    ];

    public function bootAddon()
    {
        parent::bootAddon();

        // Register view prefix
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'super-admin-toolbar');

        // SVG Icon Component
        Blade::component(Icon::class, 'sat-icon');

        // Register @superAdminToolbar blade tag
        Blade::directive('superAdminToolbar', function () {
            return "<?php echo view('super-admin-toolbar::load-toolbar')->render(); ?>";
        });
    }
}
