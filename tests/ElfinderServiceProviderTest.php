<?php

namespace Recca0120\Elfinder\Tests;

use elFinder;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\Elfinder\Options;
use Recca0120\Elfinder\Connector;
use Illuminate\Container\Container;
use Recca0120\Elfinder\ElfinderServiceProvider;

class ElfinderServiceProviderTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $container = new Container;
        $container->instance('path.config', __DIR__);
        $container->instance('path.public', __DIR__);
        Container::setInstance($container);
    }

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testRegister()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $serviceProvider = new ElfinderServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );
        $app->shouldReceive('offsetGet')->twice()->with('config')->andReturn(
            $config = m::mock('Illuminate\Contracts\Config\Repository, ArrayAccess')
        );
        $config->shouldReceive('get')->once()->with('elfinder', [])->andReturn([]);
        $config->shouldReceive('set')->once()->with('elfinder', m::type('array'));

        $app->shouldReceive('singleton')->once()->with('elfinder.options', m::on(function ($closure) use ($app) {
            $app->shouldReceive('offsetGet')->once()->with('request')->andReturn(
                $request = m::mock('Illuminate\Http\Request')
            );

            $app->shouldReceive('offsetGet')->once()->with('files')->andReturn(
                $files = m::mock('Illuminate\Filesystem\Filesystem')
            );

            $app->shouldReceive('offsetGet')->once()->with('url')->andReturn(
                $url = m::mock('Illuminate\Contracts\Routing\UrlGenerator')
            );

            $app->shouldReceive('offsetGet')->once()->with('session')->andReturn(
                $session = m::mock('\Illuminate\Session\SessionManager')
            );

            $session->shouldReceive('driver')->once();

            $app->shouldReceive('offsetGet')->once()->with('config')->andReturn(
                $config = ['elfinder' => []]
            );

            $request->shouldReceive('user')->andReturnNull();

            return $closure($app) instanceof Options;
        }));

        $app->shouldReceive('singleton')->once()->with('elfinder', m::on(function ($closure) use ($app) {
            $app->shouldReceive('offsetGet')->once()->with('elfinder.options')->andReturn([]);

            return $closure($app) instanceof elFinder;
        }));

        $app->shouldReceive('singleton')->once()->with('Recca0120\Elfinder\Connector', m::on(function ($closure) use ($app) {
            $app->shouldReceive('offsetGet')->once()->with('elfinder')->andReturn(
                $elfinder = m::mock('elFinder')
            );

            return $closure($app) instanceof Connector;
        }));

        $serviceProvider->register();
    }
}
