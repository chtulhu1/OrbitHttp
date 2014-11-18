<?php

namespace OrbitHttp;

class Config implements \IteratorAggregate
{
    private $data = array();

    public function __construct($data = null)
    {
        if (is_array($data)) {
            $this->multiset($data);
        }
    }

    public function multiset(array $data)
    {
        foreach ($data as $k => $v) {
            $this->set($k, $v);
        }
    }

    public function set($key, $val)
    {
        $this->data[$key] = $val;
    }

    public function get($key)
    {
        return $this->exists($key) ? $this->data[$key] : null;
    }

    public function clear()
    {
        $this->data = array();
    }

    public function exists($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
} 