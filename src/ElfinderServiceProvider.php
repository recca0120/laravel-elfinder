<?php

namespace Recca0120\Elfinder;

use elFinder;
use Illuminate\Support\Arr;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ElfinderServiceProvider extends ServiceProvider
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
     */
    public function boot(Router $router)
    {
        $config = $this->app['config']['elfinder'];
        $this->handleRoutes($router, $config);
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'elfinder');

        if ($this->app->runningInConsole() === true) {
            $this->handlePublishes();
        }
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/elfinder.php', 'elfinder');

        $this->app->singleton('elfinder.options', function ($app) {
            return new Options(
                $app['request'],
                $app['files'],
                $app['url'],
                array_merge($app['config']['elfinder'], [
                    'session' => new LaravelSession($app['session']),
                ])
            );
        });

        $this->app->singleton('elfinder', function ($app) {
            return new elFinder((array) $app['elfinder.options']);
        });

        $this->app->singleton(Connector::class, function ($app) {
            return new Connector($app['elfinder']);
        });
    }

    /**
     * register routes.
     *
     * @param \Illuminate\Routing\Router $router
     * @param array $config
     */
    protected function handleRoutes(Router $router, $config)
    {
        if ($this->app->routesAreCached() === false) {
            $router->group(array_merge([
                'namespace' => $this->namespace,
            ], Arr::get($config, 'route', [])), function () {
                require __DIR__.'/../routes/web.php';
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
    }
}
