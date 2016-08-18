<?php

namespace Recca0120\Elfinder;

use elFinderSessionInterface;
use Illuminate\Session\SessionManager;

class Session implements elFinderSessionInterface
{
    /**
     * $session.
     *
     * @var \\Illuminate\Session\SessionInterface
     */
    protected $session;

    /**
     * __construct.
     *
     * @method __construct
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
     * @return self
     **/
    public function start()
    {
        $this->session->start();

        return $this;
    }

    /**
     * Session write & close.
     *
     * @return self
     **/
    public function close()
    {
        $this->session->save();

        return $this;
    }

    /**
     * Get session data.
     *
     * This method must be equipped with an automatic start / close.
     *
     * @param string $key   Target key
     * @param mixed  $empty Return value of if session target key does not exist
     *
     * @return mixed
     **/
    public function get($key, $empty = '')
    {
        return $this->session->get($key, $empty);
    }

    /**
     * Set session data.
     *
     * This method must be equipped with an automatic start / close.
     *
     * @param string $key  Target key
     * @param mixed  $data Value
     *
     * @return self
     **/
    public function set($key, $data)
    {
        $this->session->set($key, $data);

        return $this;
    }

    /**
     * Get session data.
     *
     * @param string $key Target key
     *
     * @return self
     **/
    public function remove($key)
    {
        $this->session->remove($key);

        return $this;
    }
}
