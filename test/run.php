<?php

require_once dirname(dirname(__FILE__)) . '/lib/option.php';
require_once dirname(dirname(__FILE__)) . '/lib/functions.php';

define('GREAT', 1);
define('OK', 2);
define('BEEN_BETTER', 3);

$code_to_message = new Composable(function($n) {
    switch($n) {
        case GREAT: return "You did great";
        case OK: return "You did OK";
        case BEEN_BETTER: return "I've seen better";
    }
});

$exceptable = function ($n) { 
    return $n <= BEEN_BETTER and $n >= GREAT; 
};

function return_option($value) {
    if ($value) {
        return new Some($value);
    } else {
        return new None();
    }
}

function return_either($value) {
    global $exceptable;
    if ($exceptable($value)) {
        return new Right($value);
    } else {
        return new Left('Whoa! Something serious happened!');
    }
}

$print_out = function ($result) { print_r ($result . '<br/>'); };

$output = $code_to_message->then($print_out);

$always_positive = function() { return OK; };

$val = rand(1, 5);

$opt = return_option($val);

$statement = $opt->filter($exceptable)->orElse($always_positive);

$personal_message = _f::curry(function ($msg, $name) {
    return sprintf("%s, %s", $msg, $name);
});

$dump = _f::composable('var_dump');

$testing = $dump->compose($code_to_message);

$testing(GREAT);

$greetings = $personal_message->first("Hello there")->then($print_out);

$greetings("Philip Cali");

$greetings("Anna Cali");

$to_philip = $personal_message->second("Philip Cali")->then($print_out);

$to_philip($statement->map($code_to_message)->get());

$either = return_either($val);

$print_out($either->fold(_f::identity(), $code_to_message));

class Test {
    public static function add($first, $second) {
        print_r($first + $second);
    }
}

$add = _f::partial('Test::add')->apply(array('first' => 10))->then($print_out);

$add(100); // outputs 110

$str_replace = _f::partial('str_replace');

$change_lastname = $str_replace->apply(array('Cali', 'Charles'));

$change_firstname = $str_replace->apply(array('Philip', 'Bob'));

$name_change = $change_firstname->then($change_lastname)->then($print_out);

$name_change('Philip Cali'); // outputs "Bob Charles"

$name_change('Anna Cali'); // outputs "Anna Charles"

var_dump($str_replace('a', 'b', 'aaa') == str_replace('a', 'b', 'aaa'));

$rand = _f::partial('rand')->apply(array(0));
$rander = function ($x, $y) use ($rand) { return $rand($x) + $rand($y); };

$map = _f::partial('array_map');

$on_two = $map->apply(array(1 => range(1, 10), 2 => range(1, 10)));

$map($print_out, $on_two($rander));

$get_string = function ($key, $module, $a = null) {
    return "$key for $module eating $a";
};

$_s = _f::partial($get_string)->apply(array(1 => 'optional'))->then($print_out);

$_s('Jerry', 'ham sandwich');
