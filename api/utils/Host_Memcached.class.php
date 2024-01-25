<?php

class Host_Memcached
{
    // class attributes
    private $_prefix = '';
    private $_memcached = null;

    // class constructor
    public function __construct()
    {
        // set prefix
        $this->_prefix = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "";
        // init memcached
        $servers = array(
            array(
                '127.0.0.1',
                11211
            )
        );
        $this->_memcached = new Memcached();
        $this->_memcached->addServers($servers);
        $this->_memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
    }

    // class methods
    public function set($key, $value)
    {
        $this->_memcached->set($this->_prefix . $key, $value);
    }

    public function get($key)
    {
        return $this->_memcached->get($this->_prefix . $key);
    }

    public function delete($key)
    {
        return $this->_memcached->delete($this->_prefix . $key);
    }
}
