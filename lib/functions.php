<?php

require_once dirname(__FILE__) . '/option.php';

abstract class _f {
    public static function identity() {
        return function ($e) { return $e; };
    }

    public static function composable($fun) {
        return new Composable($fun);
    }

    public static function curry($fun) {
        return new Curryable($fun);
    }

    public static function partial($fun) {
        return new Partial($fun);
    }
}

abstract class AdvFun {
    protected $boxed_fun;

    public function __construct($fun) {
        if (is_callable($fun)) {
            $this->boxed_fun = self::reformat($fun);
        } else {
            throw new Exception('An advanced funciton must take a function.');
        }
    }

    public function get() {
        return $this->boxed_fun;
    }

    private static function reformat($fun) {
        if (is_string($fun) and strpos($fun, '::')) {
            return explode('::', $fun);
        } else {
            return $fun;
        }
    }
}

class Composable extends AdvFun {

    public function __invoke() {
        $boxed_fun = $this->boxed_fun;

        return call_user_func_array($boxed_fun, func_get_args());
    }

    public function compose($fun) {
        $boxed_fun = $this->boxed_fun;

        return new Composable(function () use ($boxed_fun, $fun) {
            $args = func_get_args();

            return $boxed_fun(call_user_func_array($fun, $args));
        });
    }

    public function then($fun) {
        $boxed_fun = $this->boxed_fun;

        return new Composable(function () use ($fun, $boxed_fun) {
            $args = func_get_args();

            return $fun(call_user_func_array($boxed_fun, $args));
        });
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

class Partial extends AdvFun {
    public function __invoke() {
        $boxed_fun = $this->boxed_fun;

        return call_user_func_array($boxed_fun, func_get_args());
    }

    public function apply(array $applied_args) {

        if (is_array($this->boxed_fun)) {
            list($class, $method) = $this->boxed_fun;
            $reflector = new ReflectionMethod($class, $method);
        } else if (is_subclass_of($this->boxed_fun, 'AdvFun')) {
            $reflector = new ReflectionFunction($this->boxed_fun->get());
        } else {
            $reflector = new ReflectionFunction($this->boxed_fun);
        }

        $real_params = array();
        $to_wrap = array();
        foreach ($reflector->getParameters() as $index => $arg) {
            $name = $arg->getName();

            if (isset($applied_args[$name])) {
                $real_params[$index] = $applied_args[$name];
            } else if (isset($applied_args[$index])) {
                $real_params[$index] = $applied_args[$index];
            } else {
                $to_wrap[] = $index;
            }
        }

        $boxed_fun = $this->boxed_fun;

        return new Composable(function() use ($boxed_fun, $to_wrap, $real_params) {
            $called = func_get_args();

            $remaining_args = array();
            foreach ($called as $index => $arg) {
                $remaining_args[$to_wrap[$index]] = $arg;
            }

            $together = $real_params + $remaining_args;
            ksort($together);

            return call_user_func_array($boxed_fun, $together);
        });
    }
}
