<?php

namespace Recca0120\Elfinder;

use elFinderSessionInterface;
use Illuminate\Session\SessionManager;

class LaravelSession implements elFinderSessionInterface
{
    /**
     * $session.
     *
     * @var \Illuminate\Session\SessionInterface
     */
    protected $session;

    /**
     * __construct.
     *
     * @param \Illuminate\Session\SessionManager $sessionManager
     */
    public function __construct(SessionManager $sessionManager)
    {
        $this->session = $sessionManager->driver();
    }

    /**
     * Session start.
     *
     * @return $this
     **/
    public function start()
    {
        if ($this->session->isStarted() === false) {
            $this->session->start();
        }

        return $this;
    }

    /**
     * Session write & close.
     *
     * @return $this
     **/
    public function close()
    {
        if ($this->session->isStarted() === true) {
            $this->session->save();
        }

        return $this;
    }

    /**
     * Get session data.
     *
     * This method must be equipped with an automatic start / close.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     **/
    public function get($key, $default = '')
    {
        return $this->session->get($key, $default);
    }

    /**
     * Set session data.
     *
     * This method must be equipped with an automatic start / close.
     *
     * @param string $key
     * @param mixed $data
     * @return $this
     **/
    public function set($key, $data)
    {
        $this->session->put($key, $data);

        return $this;
    }

    /**
     * Get session data.
     *
     * @param string $key
     * @return $this
     **/
    public function remove($key)
    {
        $this->session->remove($key);

        return $this;
    }
}
