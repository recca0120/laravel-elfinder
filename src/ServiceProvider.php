<?php

namespace Recca0120\Elfinder;

use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
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
     * handle routes.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $config = $this->app['config']->get('elfinder', []);
        $this->handlePublishes();
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'elfinder');
        $this->handleRoutes($router, $config);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/elfinder.php', 'elfinder');
    }

    /**
     * register routes.
     *
     * @param \Illuminate\Routing\Router $router
     * @oaram array                      $config
     *
     * @return void
     */
    public function handleRoutes(Router $router, $config)
    {
        if ($this->app->routesAreCached() === false) {
            $router->group(array_merge([
                'middleware' => ['web', 'auth'],
                'prefix'     => 'elfinder',
                'as'         => 'elfinder.',
                'namespace'  => $this->namespace,
            ], Arr::get($config, 'elfinder.route', [])), function () {
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
            __DIR__.'/../resources/views' => $this->app->basePath().'/resources/views/vendor/elfinder',
        ], 'views');

        $this->publishes([
            __DIR__.'/../resources/elfinder' => $this->app->publicPath().'/vendor/elfinder',
        ], 'public');

        $this->publishes([
            __DIR__.'/../config/elfinder.php' => $this->app->configPath().'/elfinder.php',
        ], 'config');
    }
}
