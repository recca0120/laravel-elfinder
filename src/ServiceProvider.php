<?php

namespace Recca0120\Elfinder;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    protected $namespace = 'Recca0120\Elfinder\Http\Controllers';

    public function boot(Router $router)
    {
        // require __DIR__.'/../php/elFinderConnector.class.php';
        // require __DIR__.'/../php/elFinder.class.php';
        // require __DIR__.'/../php/elFinderVolumeDriver.class.php';
        // require __DIR__.'/../php/elFinderVolumeLocalFileSystem.class.php';
        // require __DIR__.'/../php/elFinderVolumeMySQL.class.php';
        // require __DIR__.'/../php/elFinderVolumeFTP.class.php';
        // require __DIR__.'/../php/elFinderVolumeDropbox.class.php';

        if (! defined('ELFINDER_IMG_PARENT_URL')) {
            define('ELFINDER_IMG_PARENT_URL', asset('vendor/elfinder'));
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'elfinder');
        $this->map($router);
        $this->publish();
    }

    protected function map($router)
    {
        if ($this->app->routesAreCached() === false) {
            $prefix = 'elfinder';
            $group = $router->group([
                'namespace' => $this->namespace,
                'as' => 'elfinder::',
                'prefix' => $prefix,
            ], function () {
                require __DIR__.'/Http/routes.php';
            });
        }
    }

    protected function publish()
    {
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/elfinder'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/elfinder'),
        ], 'public');
    }

    public function register()
    {
    }
}
