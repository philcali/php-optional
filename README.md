# Optional Types and Function Composition

The PHP optional package aims at creating optional types in PHP the language.
The `Option` and `Either` types are heavily inspired from types from the
[Scala][Scala] Programming Language.

[Scala]: http://www.scala-lang.org/

## Options

Consider the following code snippet:

```
define('GREAT', 1);
define('OK', 2);
define('BEEN_BETTER', 3);

$code_to_message = function($n) {
    switch($n) {
        case GREAT: return "You did great";
        case OK: return "You did OK";
        case BEEN_BETTER: return "I've seen better";
    }
};

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

$always_positive = function() { return OK; };

$output = function($result) { echo "$result <br/>"; };

$opt = return_option(rand(0, 5));

$statement = $opt->filter($exceptable)->orElse($always_positive);

$statement->map($code_to_message)->each($output);
```

You can always be sure that `$statement` will be a valid type without
polluting the code base with conditionals.

## Either

The `Either` type takes an `Option` from _something to nothing_, to _either
something or something else_.

We'll change our base example above to use an `Either` instead.

```
....
function return_either($value) {
    if ($value) {
        return new Right($value);
    } else {
        return new Left("Whoa! Something happened");
    }
}

$either = return_either(rand(0, 5));

$result = $either->fold(_f::identity(), $code_to_message);

$output($result);
```

## Composables

One can wrap an invokeable with a `new Composable` or ```_f::composable```to 
create a composable function. A `Composable` can be invoked like a regular 
function, yet the user has composability.

Let's take a look at an example, using the above code: 

```
$code_to_message = _f::composable(function($n) {
    switch($n) {
        case GREAT: return "You did great";
        case OK: return "You did OK";
        case BEEN_BETTER: return "I've seen better";
    }
});

....

$output = function($result) { echo "$result <br/>"; };

$full_output = $code_to_message->then($output);

$opt = return_option(rand(0, 5));

$opt->filter($exceptable)->orElse($always_positive)->each($full_output);

$dump = _f::composable('var_dump');

$testing = $dump->compose($code_to_message);

$testing(GREAT); // string(13) "You did great"
``` 

## Curryable

One can wrap a two parameter function with a `new Curryable` or ```_f::curry``` 
to create a currying function, whose curried functions are composable.

Let's take a look at an example, using the above code:

```
....
$opt = return_option(rand(0, 5));

$statement = $opt->filter($exceptable)->orElse($always_positive);

$personal_message = _f::curry(function ($msg, $name) {
  return sprintf("%s, %s", $msg, $name);
});

$greetings = $personal_message->first("Hello there")->then($output);

$greetings("Philip Cali");

$greetings("Other doodz");

$to_philip = $personal_message->second("Philip Cali")->then($output);

$to_philip($statement->map($code_to_message)->get());
```

## Partials

One can create partially applied function with `new Partial` or ```_f::partial```.

Let's take a look at an example called a static method on a class and a
built-in function. Of course it works with closures, too.

```
class Test {
    public static function add($first, $second) {
        print_r($first + $second);
    }
}

$add = _f::partial('Test::add')->apply(array(10))->then($output);

$add(100); // outputs 110

$str_replace = _f::partial('str_replace');

$change_lastname = $str_replace->apply(array('Cali', 'Charles'));

$change_firstname = $str_replace->apply(array('Philip', 'Bob'));

$name_change = $change_firstname->then($change_lastname)->then($output);

$name_change('Philip Cali'); // outputs "Bob Charles"

$name_change('Anna Cali'); // outputs "Anna Charles"

var_dump($str_replace('a', 'b', 'aaa') == str_replace('a', 'b', 'aaa')); // true
```

---

Copyright 2011, Philip Cali
