<?php

require_once dirname(__FILE__) . '/either.php';

interface Monadic {
    function map($transform);

    function filter($filter);

    function each($iter);
}

abstract class Option implements Monadic {
    abstract function isEmpty();

    abstract function get();

    function getOrElse($default) {
        if ($this->isEmpty()) {
            return $default;
        } else {
            return $this->get();
        }
    }

    function orElse($body) {
        if ($this->isEmpty()) {
            return new Some($body());
        } else {
            return $this;
        }
    }

    function map($transform) {
        if ($this->isEmpty()) {
            return new None();
        } else {
            return new Some($transform($this->get()));
        }
    }

    function filter($filter) {
        if ($this->isEmpty()) {
            return new None();
        } else if ($filter($this->get())) {
            return new Some($this->get());
        } else {
            return new None();
        }
    }

    function each($iter) {
        if ($this->isEmpty()) {
            return new None();
        } else {
            $iter($this->get());
            return new Some($this->get());
        }
    }

    function toLeft($to_right) {
        if ($this->isEmpty()) {
            return new Right($to_right());
        } else {
            return new Left($this->get());
        }
    }

    function toRight($to_left) {
        if ($this->isEmpty()) {
            return new Left($to_left());
        } else {
            return new Right($this->get());
        }
    }
}

class None extends Option {
    function get() {
        throw new Exception('Cannot call None->get()');
    }

    function isEmpty() {
        return true;
    }
}

class Some extends Option {
    private $identity;

    function __construct($value) {
        $this->identity = $value;
    }

    function get() {
        return $this->identity;
    }

    function isEmpty() {
        return false;
    }
}
