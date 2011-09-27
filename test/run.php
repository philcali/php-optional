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

$print_out = function ($result) { print_r ($result . '<br/>'); };

$output = $code_to_message->andThen($print_out);

$always_positive = function() { return OK; };

$val = rand(1, 5);

$opt = return_option($val);

$statement = $opt->filter($exceptable)->orElse($always_positive);

$personal_message = new Curryable(function ($msg, $name) {
  return sprintf("%s, %s", $msg, $name);
});

$greetings = $personal_message->first("Hello there")->andThen($print_out);

$greetings("Philip Cali");

$greetings("Anna Cali");

$to_philip = $personal_message->second("Philip Cali")->andThen($print_out);

$to_philip($statement->map($code_to_message)->get());
