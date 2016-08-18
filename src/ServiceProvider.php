<?php

namespace Recca0120\Elfinder;

use Illuminate\Contracts\Config\Repository as ConfigContract;
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
     * handle routes.
     *
     * @param \Illuminate\Routing\Router              $router
     * @param \Illuminate\Contracts\Config\Repository $config
     *
     * @return void
     */
    public function boot(Router $router, ConfigContract $config)
    {
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
        require __DIR__.'/../resources/elfinder/php/autoload.php';
    }

    /**
     * register routes.
     *
     * @param Illuminate\Routing\Router               $router
     * @param \Illuminate\Contracts\Config\Repository $config
     *
     * @return void
     */
    public function handleRoutes(Router $router, ConfigContract $config)
    {
        if ($this->app->routesAreCached() === false) {
            $router->group(array_merge($config->get('elfinder.router'), [
                'as'         => 'elfinder.',
                'namespace'  => $this->namespace,
            ]), function () {
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
