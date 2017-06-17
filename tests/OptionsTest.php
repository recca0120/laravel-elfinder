<?php

namespace Recca0120\Elfinder\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\Elfinder\Options;
use Illuminate\Container\Container;

class OptionsTest extends TestCase
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

    public function testOptions()
    {
        $config = [
            'route' => [
                'prefix' => 'elfinder',
                'as' => 'elfinder.',
                'middleware' => ['web', 'auth'],
            ],
            'accessControl' => $accessControl = 'foo.accessControl',
            'options' => [
                'locale' => 'en_US.UTF-8',
                'debug' => false,
                'roots' => [
                    [
                        'driver' => 'LocalFileSystem',
                        'alias' => 'Home',
                        'rootCssClass' => 'elfinder-button-icon-home',
                        'path' => public_path('storage/media/user/{user_id}'),
                        'URL' => 'storage/media/user/{user_id}',
                    ], [
                        'driver' => 'LocalFileSystem',
                        'alias' => 'Shared',
                        'path' => public_path('storage/media/shared'),
                        'URL' => 'storage/media/shared',
                    ], [
                        'driver' => 'Trash',
                        'path' => public_path('storage/media/trash'),
                        'URL' => 'storage/media/trash',
                    ],
                ],
            ],
        ];

        $request = m::mock('Illuminate\Http\Request');
        $files = m::mock('Illuminate\Filesystem\Filesystem');
        $urlGenerator = m::mock('Illuminate\Contracts\Routing\UrlGenerator');

        $request->shouldReceive('user')->once()->andReturn(
            $user = m::mock('stdClass')
        );
        $user->id = 'foo';

        $files->shouldReceive('exists')->atLeast()->andReturn(false);
        $files->shouldReceive('makeDirectory')->atLeast()->andReturn(false);

        $urlGenerator->shouldReceive('to')->atLeast()->andReturnUsing(function ($path) {
            return 'path/'.$path;
        });

        $this->assertSame([
            'locale' => 'en_US.UTF-8',
            'debug' => false,
            'roots' => [
                [
                    'accessControl' => $accessControl,
                    'autoload' => true,
                    'mimeDetect' => 'internal',
                    'tmbBgColor' => 'transparent',
                    'tmbCrop' => false,
                    'utf8fix' => true,
                    'driver' => 'LocalFileSystem',
                    'alias' => 'Home',
                    'rootCssClass' => 'elfinder-button-icon-home',
                    'path' => public_path('storage/media/user/'.$user->id),
                    'URL' => 'path/storage/media/user/'.$user->id,
                ], [
                    'accessControl' => $accessControl,
                    'autoload' => true,
                    'mimeDetect' => 'internal',
                    'tmbBgColor' => 'transparent',
                    'tmbCrop' => false,
                    'utf8fix' => true,
                    'driver' => 'LocalFileSystem',
                    'alias' => 'Shared',
                    'path' => public_path('storage/media/shared'),
                    'URL' => 'path/storage/media/shared',
                ], [
                    'accessControl' => $accessControl,
                    'autoload' => true,
                    'mimeDetect' => 'internal',
                    'tmbBgColor' => 'transparent',
                    'tmbCrop' => false,
                    'utf8fix' => true,
                    'id' => 1,
                    'driver' => 'Trash',
                    'path' => public_path('storage/media/trash'),
                    'URL' => 'path/storage/media/trash',
                ],
            ],
        ], (array) new Options($request, $files, $urlGenerator, $config));
    }

    public function testOptionsWithoutUser()
    {
        $config = [
            'route' => [
                'prefix' => 'elfinder',
                'as' => 'elfinder.',
                'middleware' => ['web', 'auth'],
            ],
            'accessControl' => $accessControl = 'foo.accessControl',
            'options' => [
                'locale' => 'en_US.UTF-8',
                'debug' => false,
                'roots' => [
                    [
                        'driver' => 'LocalFileSystem',
                        'alias' => 'Home',
                        'rootCssClass' => 'elfinder-button-icon-home',
                        'path' => public_path('storage/media/user/{user_id}'),
                        'URL' => 'storage/media/user/{user_id}',
                    ], [
                        'driver' => 'LocalFileSystem',
                        'alias' => 'Shared',
                        'path' => public_path('storage/media/shared'),
                        'URL' => 'storage/media/shared',
                    ], [
                        'driver' => 'Trash',
                        'path' => public_path('storage/media/trash'),
                        'URL' => 'storage/media/trash',
                    ],
                ],
            ],
        ];

        $request = m::mock('Illuminate\Http\Request');
        $files = m::mock('Illuminate\Filesystem\Filesystem');
        $urlGenerator = m::mock('Illuminate\Contracts\Routing\UrlGenerator');

        $request->shouldReceive('user')->once()->andReturn(null);

        $files->shouldReceive('exists')->atLeast()->andReturn(false);
        $files->shouldReceive('makeDirectory')->atLeast()->andReturn(false);

        $urlGenerator->shouldReceive('to')->atLeast()->andReturnUsing(function ($path) {
            return 'path/'.$path;
        });

        $this->assertSame([
            'locale' => 'en_US.UTF-8',
            'debug' => false,
            'roots' => [
                [
                    'accessControl' => $accessControl,
                    'autoload' => true,
                    'mimeDetect' => 'internal',
                    'tmbBgColor' => 'transparent',
                    'tmbCrop' => false,
                    'utf8fix' => true,
                    'driver' => 'LocalFileSystem',
                    'alias' => 'Shared',
                    'path' => public_path('storage/media/shared'),
                    'URL' => 'path/storage/media/shared',
                ], [
                    'accessControl' => $accessControl,
                    'autoload' => true,
                    'mimeDetect' => 'internal',
                    'tmbBgColor' => 'transparent',
                    'tmbCrop' => false,
                    'utf8fix' => true,
                    'id' => 1,
                    'driver' => 'Trash',
                    'path' => public_path('storage/media/trash'),
                    'URL' => 'path/storage/media/trash',
                ],
            ],
        ], (array) new Options($request, $files, $urlGenerator, $config));
    }
}
