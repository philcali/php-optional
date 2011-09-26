<?php

abstract class AdvFun {
    protected $boxed_fun;

    function __construct($fun) {
        $this->boxed_fun = $fun;
    }
}

class Composable extends AdvFun {

    public function __invoke($param) {
        $boxed_fun = $this->boxed_fun;

        return $boxed_fun($param);
    }

    public function compose($fun) {
        $boxed_fun = $this->boxed_fun;

        return function ($x) use ($boxed_fun, $fun) {
            return $boxed_fun($fun($x));
        };
    }

    public function andThen($fun) {
        $boxed_fun = $this->boxed_fun;

        return function ($x) use ($fun, $boxed_fun) {
            return $fun($boxed_fun($x));
        };
    }
}

class Curryable extends AdvFun {

    public function __invoke($x, $y) {
        $boxed_fun = $this->boxed_fun;

        return $boxed_fun($x, $y);
    }

    public function curried() {
        $boxed_fun = $this->boxed_fun;

        return new Composable(function ($x) use ($boxed_fun) {
            return new Composable(function ($y) use ($x, $boxed_fun) {
                return $boxed_fun($x, $y);
            });
        });
    }

    public function swap() {
        $boxed_fun = $this->boxed_fun;

        return new Curryable(function($y, $x) use ($boxed_fun) {
            return $boxed_fun($x, $y);
        });
    }

    public function first($x) {
        $curried = $this->curried();

        return $curried($x);
    }

    public function second($y) {
        $curried = $this->swap()->curried();

        return $curried($y);
    }
}
