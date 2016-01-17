<?php

namespace Recca0120\Elfinder;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * namespace.
     *
     * @var string
     */
    protected $namespace = 'Recca0120\Elfinder\Http\Controllers';

    /**
     * prefix.
     *
     * @var string
     */
    protected $prefix = 'elfinder';

    /**
     * handle routes.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'elfinder');
        $this->handlePublishes();
        $this->handleRoutes($router);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * handle routes.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    protected function handleRoutes(Router $router)
    {
        if ($this->app->routesAreCached() === false) {
            $group = $router->group([
                'namespace' => $this->namespace,
                'as'        => 'elfinder::',
                'prefix'    => $this->prefix,
            ], function () {
                require __DIR__.'/Http/routes.php';
            });
        }
    }

    /**
     * handle publishes.
     *
     * @return void
     */
    protected function handlePublishes()
    {
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/elfinder'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../resources/elfinder' => public_path('vendor/elfinder'),
        ], 'public');

        $this->publishes([
            __DIR__.'/../config/elfinder.php' => config_path('elfinder.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/elfinder.php', 'elfinder');
    }
}
