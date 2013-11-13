<?php

// Container Class
// Used to store things in a key -> value array
class Container {
    private $container = array();

    public function __set($key, $value) {
        $this->container[$key] = $value;
    }

    public function __get($key) {
        if (array_key_exists($key, $this->container)) {
            return $this->container[$key];
        }

        return null;
    }

    public function __isset($key) {
        return isset($this->container[$key]);
    }

    public function __unset($key) {
        unset($this->container[$key]);
    }
}
?>
