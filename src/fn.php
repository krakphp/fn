<?php

namespace Krak\Fn;

// ACCESS

function method($name, /* object */ $data, ...$optionalArgs) {
    return $data->{$name}(...$optionalArgs);
}
function prop(string $key, /* object */ $data, $else = null) {
    return property_exists($key, $data) ? $data->{$key} : $else;
}
function index(/* string|int */ $key, array $data, $else = null) {
    return array_key_exists($key, $data) ? $data[$key] : $else;
}

function propIn(array $keys, /* object */ $data, $else = null) {
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

// SLICING

function takeWhile(callable $predicate, iterable $iter): iterable {
    foreach ($iter as $k => $v) {
        if ($predicate($v)) {
            yield $k => $v;
        } else {
            return;
        }
    }
}

function dropWhile(callable $predicate, iterable $iter): iterable {
    $stillDropping = true;
    foreach ($iter as $k => $v) {
        if ($stillDropping && $predicate($v)) {
            continue;
        } else if ($stillDropping) {
            $stillDropping = false;
        }

        yield $k => $v;
    }
}

function take(int $num, iterable $iter): iterable {
    return slice(0, $iter, $num);
}

function drop(int $num, iterable $iter): iterable {
    return slice($num, $iter);
}

function slice(int $start, iterable $iter, $length = INF): iterable {
    assert($start >= 0);

    $i = 0;
    $end = $start + $length - 1;
    foreach ($iter as $k => $v) {
        if ($start <= $i && $i <= $end) {
            yield $k => $v;
        }
        $i += 1;
    }
}

function head(iterable $iter) {
    foreach ($iter as $v) {
        return $v;
    }
}

function chunk(int $size, iterable $iter): iterable {
    assert($size > 0);

    $chunk = [];
    foreach ($iter as $v) {
        $chunk[] = $v;
        if (\count($chunk) == $size) {
            yield $chunk;
            $chunk = [];
        }
    }

    if ($chunk) {
        yield $chunk;
    }
}


// GENERATORS

function range($start, $end, $step = null) {
    if ($start == $end) {
        yield $start;
    } else if ($start < $end) {
        $step = $step ?: 1;
        if ($step <= 0) {
            throw new \InvalidArgumentException('Step must be greater than 0.');
        }
        for ($i = $start; $i <= $end; $i += $step) {
            yield $i;
        }
    } else {
        $step = $step ?: -1;
        if ($step >= 0) {
            throw new \InvalidArgumentException('Step must be less than 0.');
        }
        for ($i = $start; $i >= $end; $i += $step) {
            yield $i;
        }
    }
}

// OPERATORS

function op(string $op, $b, $a) {
    switch ($op) {
    case '==':
    case 'eq':
        return $a == $b;
    case '!=':
    case 'neq':
        return $a != $b;
    case '===':
        return $a === $b;
    case '!==':
        return $a !== $b;
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

function andf(callable ...$fns) {
    return function($el) use ($fns) {
        foreach ($fns as $fn) {
            if (!$fn($el)) {
                return false;
            }
        }
        return true;
    };
}
function orf(callable ...$fns) {
    return function($el) use ($fns) {
        foreach ($fns as $fn) {
            if ($fn($el)) {
                return true;
            }
        }
        return false;
    };
}



function chain(iterable ...$iters) {
    foreach ($iters as $iter) {
        foreach ($iter as $k => $v) {
            yield $k => $v;
        }
    }
}

function flatMap(callable $map, iterable $iter): iterable {
    foreach ($iter as $k => $v) {
        foreach ($map($v) as $k => $v) {
            yield $k => $v;
        }
    }
}

function flatten(iterable $iter, $levels = INF) {
    if ($levels == 0) {
        return $iter;
    } else if ($levels == 1) {
        foreach ($iter as $k => $v) {
            if (\is_iterable($v)) {
                foreach ($v as $k1 => $v1) {
                    yield $k1 => $v1;
                }
            } else {
                yield $k => $v;
            }
        }
    } else {
        foreach ($iter as $k => $v) {
            if (\is_iterable($v)) {
                foreach (flatten($v, $levels - 1) as $k1 => $v1) {
                    yield $k1 => $v1;
                }
            } else {
                yield $k => $v;
            }
        }
    }
}


function when(callable $if, callable $then, $value) {
    return $if($value) ? $then($value) : $value;
}

function toPairs(iterable $iter): iterable {
    foreach ($iter as $key => $val) {
        yield [$key, $val];
    }
}
function fromPairs(iterable $iter): iterable {
    foreach ($iter as list($key, $val)) {
        yield $key => $val;
    }
}

function without(array $fields, iterable $iter): iterable {
    foreach ($iter as $k => $v) {
        if (!\in_array($k, $fields)) {
            yield $k => $v;
        }
    }
}


function inArray(array $set, $item): bool {
    return \in_array($item, $set);
}

function all(callable $predicate, iterable $iter): bool {
    foreach ($iter as $key => $value) {
        if (!$predicate($value)) {
            return false;
        }
    }

    return true;
}
function any(callable $predicate, iterable $iter): bool {
    foreach ($iter as $key => $value) {
        if ($predicate($value)) {
            return true;
        }
    }

    return false;
}
function search(callable $predicate, iterable $iter) {
    foreach ($iter as $value) {
        if ($predicate($value)) {
            return $value;
        }
    }
}

function trans(callable $trans, callable $fn, $data) {
    return $fn($trans($data));
}
function not(callable $fn, ...$args): bool {
    return !$fn(...$args);
}
function isInstance($class, $item) {
    return $item instanceof $class;
}

function isNull($val) {
    return \is_null($item);
}

function partition(callable $partition, iterable $iter, int $numParts = 2): array {
    $parts = array_fill(0, $numParts, []);
    foreach ($iter as $val) {
        $index = (int) $partition($val);
        $parts[$index][] = $val;
    }

    return $parts;
}

function map(callable $predicate, iterable $iter): iterable {
    foreach ($iter as $key => $value) {
        yield $key => $predicate($value);
    }
}

function mapKeys(callable $predicate, iterable $iter): iterable {
    foreach ($iter as $key => $value) {
        yield $predicate($key) => $value;
    }
}

function reduce(callable $reduce, iterable $iter, $acc = null) {
    foreach ($data as $key => $value) {
        $acc = $reduce($acc, $value);
    }
    return $acc;
}

function filter(callable $predicate, iterable $iter): iterable {
    foreach ($iter as $key => $value) {
        if ($predicate($value)) {
            yield $key => $value;
        }
    }
}
function filterKeys(callable $predicate, iterable $iter): iterable {
    foreach ($data as $key => $value) {
        if ($predicate($key)) {
            yield $key => $value;
        }
    }
}

function curry(callable $fn, int $num = 1) {
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

function toArray(iterable $iter): array {
    $data = [];
    foreach ($iter as $key => $val) {
        $data[] = $val;
    }
    return $data;
}

function toArrayWithKeys(iterable $iter): array {
    $data = [];
    foreach ($iter as $key => $val) {
        $data[$key] = $val;
    }
    return $data;
}

function id($v) {
    return $v;
}

function pipe(callable ...$fns) {
    return function($arg) use ($fns) {
        foreach ($fns as $fn) {
            $arg = $fn($arg);
        }
        return $arg;
    };
}

function compose(callable ...$fns) {
    return pipe(...array_reverse($fns));
}

function stack(array $funcs, callable $last = null, callable $resolve = null) {
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

function onEach(callable $handle, iterable $iter) {
    foreach ($iter as $v) {
        $handle($v);
    }
}
