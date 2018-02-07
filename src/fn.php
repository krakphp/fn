<?php

namespace Krak\Fn;

function prop(string $key, $data, $else = null) {
    return $data->{$key};
    return property_exists($key, $data) ? $data->{$key} : $else;
}
function index($key, array $data, $else = null) {
    return array_key_exists($key, $data) ? $data[$key] : $else;
}

function propIn(array $keys, $data, $else = null) {
    foreach ($props as $prop) {
        if (!is_object($obj) || !isset($obj->{$prop})) {
            return $else;
        }

        $obj = $obj->{$prop};
    }

    return $obj;
}

function indexIn(array $keys, array $data, $else = null) {
    foreach ($keys as $part) {
        if (!is_array($data) || !array_key_exists($part, $data)) {
            return $else;
        }

        $data = $data[$part];
    }

    return $data;
}

function head($iterable) {
    foreach ($iterable as $v) {
        return $v;
    }
}

function toPairs($iterable) {
    foreach ($iterable as $key => $val) {
        yield [$key, $val];
    }
}
function fromPairs($iterable) {
    foreach ($iterable as list($key, $val)) {
        yield $key => $val;
    }
}

function without(array $fields, $data) {
    return fromPairs(filter(function($tup) use ($fields) {
        return !\in_array($tup[0], $fields);
    }, toPairs($data)));
}

function op($op, $b, $a) {
    switch ($op) {
    case '==':
    case 'eq':
        return $a == $b;
    case '===':
        return $a === $b;
    case '>':
    case 'gt':
        return $a > $b;
    case '>=':
    case 'gte':
        return $a >= $b;
    case '<':
    case 'lt':
        return $a < $b;
    case '<=':
    case 'lte':
        return $a <= $b;
    case '+':
        return $a + $b;
    case '-':
        return $a - $b;
    case '*':
        return $a * $b;
    case '**':
        return $a ** $b;
    case '/':
        return $a / $b;
    case '%':
        return $a % $b;
    default:
        throw new \LogicException('Invalid operator '.$op);
    }
}

function inArray(array $set, $item) {
    return \in_array($item, $set);
}

function andf(...$fns) {
    return function($el) use ($fns) {
        foreach ($fns as $fn) {
            if (!$fn($el)) {
                return false;
            }
        }
        return true;
    };
}
function orf(...$fns) {
    return function($el) use ($fns) {
        foreach ($fns as $fn) {
            if ($fn($el)) {
                return true;
            }
        }
        return false;
    };
}
function all(callable $predicate, $data) {
    foreach ($data as $key => $value) {
        if (!$predicate($value)) {
            return false;
        }
    }

    return true;
}
function any(callable $predicate, $data) {
    foreach ($data as $key => $value) {
        if ($predicate($value)) {
            return true;
        }
    }

    return false;
}
/** yas */
function trans(callable $trans, callable $fn, $data) {
    return $fn($trans($data));
}
function not(callable $fn, ...$args) {
    return !$fn(...$args);
}
function isInstance($class, $item) {
    return $item instanceof $class;
}

function isNull($val) {
    return \is_null($item);
}

function partition(callable $partition, $data, $numParts = 2) {
    $parts = array_fill(0, $numParts, []);
    foreach ($data as $val) {
        $index = (int) $partition($val);
        $parts[$index][] = $val;
    }

    return $parts;
}

function search(callable $predicate, $data) {
    foreach ($data as $value) {
        if ($predicate($value)) {
            return $value;
        }
    }
}

function map(callable $predicate, $data) {
    foreach ($data as $key => $value) {
        yield $key => $predicate($value);
    }
}

function mapKeys(callable $predicate, $data) {
    foreach ($data as $key => $value) {
        yield $predicate($key) => $value;
    }
}

function reduce(callable $reduce, $data, $acc = null) {
    foreach ($data as $key => $value) {
        $acc = $reduce($acc, $value);
    }
    return $acc;
}

function filter(callable $predicate, $data) {
    foreach ($data as $key => $value) {
        if ($predicate($value)) {
            yield $key => $value;
        }
    }
}
function filterKeys(callable $predicate, $data) {
    foreach ($data as $key => $value) {
        if ($predicate($key)) {
            yield $key => $value;
        }
    }
}

function curry(callable $fn, $num = 1) {
    if ($num == 0) {
        return $fn;
    }

    return function($arg1) use ($fn, $num) {
        return curry(function(...$args) use ($fn, $arg1) {
            return $fn($arg1, ...$args);
        }, $num - 1);
    };
}

function placeholder() {
    static $v;

    $v = $v ?: new class {};
    return $v;
}
function _() {
    return placeholder();
}

function partial(callable $fn, ...$appliedArgs) {
    return function(...$args) use ($fn, $appliedArgs) {
        list($appliedArgs, $args) = array_reduce($appliedArgs, function($acc, $arg) {
            list($appliedArgs, $args) = $acc;
            if ($arg === placeholder()) {
                $arg = array_shift($args);
            }

            $appliedArgs[] = $arg;
            return [$appliedArgs, $args];
        }, [[], $args]);

        return $fn(...$appliedArgs, ...$args);
    };
}

function autoCurry(array $args, $numArgs, callable $fn) {
    if (count($args) >= $numArgs) {
        return $fn(...$args);
    }
    if (count($args) == $numArgs - 1) {
        return partial($fn, ...$args);
    }
    if (count($args) == 0) {
        return curry($fn, $numArgs - 1);
    }

    return curry(
        partial($fn, ...$args),
        ($numArgs - 1 - count($args))
    );
}

function toArray($iter) {
    $data = [];
    foreach ($iter as $key => $val) {
        $data[] = $val;
    }
    return $data;
}

function toArrayWithKeys($iter) {
    $data = [];
    foreach ($iter as $key => $val) {
        $data[$key] = $val;
    }
    return $data;
}

function id($v) {
    return $v;
}

function pipe(...$fns) {
    return function($arg) use ($fns) {
        foreach ($fns as $fn) {
            $arg = $fn($arg);
        }
        return $arg;
    };
}

function compose(...$fns) {
    return pipe(...array_reverse($fns));
}

function stack($funcs, callable $last = null, callable $resolve = null) {
    return function(...$args) use ($funcs, $resolve, $last) {
        return reduce(function($acc, $func) use ($resolve) {
            return function(...$args) use ($acc, $func, $resolve) {
                $args[] = $acc;
                $func = $resolve ? $resolve($func) : $func;
                return $func(...$args);
            };
        }, $funcs, $last ?: function() { throw new \LogicException('No stack handler was able to capture this request'); });
    };
}
