<?php

require_once dirname(__FILE__) . '/option.php';

abstract class Either {
    protected $identity;

    abstract function isLeft();

    function fold($left, $right) {
        if ($this->isLeft()) {
            return $left($this->get());
        } else {
            return $right($this->get());
        }
    }

    function isRight() {
        return !$this->isLeft();
    }

    function get() {
        return $this->identity;
    }

    function toOption() {
        if ($this->isRight()) {
            return new Some($this->get());
        } else {
            return new None();
        }
    }
}

class Left extends Either {
    function __construct($value) {
        $this->identity = $value;
    }

    function isLeft() {
        return true;
    }
}

class Right extends Either {
    function __construct($value) {
        $this->identity = $value;
    }

    function isLeft() {
        return false;
    }
}
