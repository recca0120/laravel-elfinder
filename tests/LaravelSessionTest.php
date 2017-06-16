<?php

namespace Recca0120\Elfinder\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\Elfinder\LaravelSession;

class LaravelSessionTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->session = m::mock('Illuminate\Session\SessionManager');
        $this->session->shouldReceive('driver')->once()->andReturnSelf();
    }

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testStart()
    {
        $laravelSession = new LaravelSession($this->session);

        $this->session->shouldReceive('isStarted')->once()->andReturn(false);
        $this->session->shouldReceive('start')->once();

        $this->assertSame($laravelSession->start(), $laravelSession);
    }

    public function testClose()
    {
        $laravelSession = new LaravelSession($this->session);

        $this->session->shouldReceive('isStarted')->once()->andReturn(true);
        $this->session->shouldReceive('save')->once();

        $this->assertSame($laravelSession->close(), $laravelSession);
    }

    public function testGet()
    {
        $laravelSession = new LaravelSession($this->session);

        $key = 'foo';
        $value = 'bar';
        $default = 'buzz';

        $this->session->shouldReceive('get')->once()->with($key, $default)->andReturn($value);

        $this->assertSame($laravelSession->get($key, $default), $value);
    }

    public function testSet()
    {
        $laravelSession = new LaravelSession($this->session);

        $key = 'foo';
        $value = 'bar';

        $this->session->shouldReceive('put')->once()->with($key, $value);

        $this->assertSame($laravelSession->set($key, $value), $laravelSession);
    }

    public function testRemove()
    {
        $laravelSession = new LaravelSession($this->session);

        $key = 'foo';

        $this->session->shouldReceive('remove')->once()->with($key);

        $this->assertSame($laravelSession->remove($key), $laravelSession);
    }
}
