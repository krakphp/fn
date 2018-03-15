<?php

namespace Krak\Fn;

// ACCESS

function method($name, /* object */ $data, ...$optionalArgs) {
    return $data->{$name}(...$optionalArgs);
}
function prop(string $key, /* object */ $data, $else = null) {
    return \property_exists($data, $key) ? $data->{$key} : $else;
}
function index(/* string|int */ $key, array $data, $else = null) {
    return \array_key_exists($key, $data) ? $data[$key] : $else;
}

function propIn(array $props, /* object */ $obj, $else = null) {
    foreach ($props as $prop) {
        if (!\is_object($obj) || !\property_exists($obj, $prop)) {
            return $else;
        }

        $obj = $obj->{$prop};
    }

    return $obj;
}

function indexIn(array $keys, array $data, $else = null) {
    foreach ($keys as $part) {
        if (!\is_array($data) || !\array_key_exists($part, $data)) {
            return $else;
        }

        $data = $data[$part];
    }

    return $data;
}

function hasIndexIn(array $keys, array $data): bool {
    foreach ($keys as $key) {
        if (!\is_array($data) || !\array_key_exists($key, $data)) {
            return false;
        }
        $data = $data[$key];
    }

    return true;
}

function updateIndexIn(array $keys, callable $update, array $data): array {
    $curData = &$data;
    foreach (\array_slice($keys, 0, -1) as $key) {
        if (!\array_key_exists($key, $curData)) {
            throw new \RuntimeException('Could not updateIn because the keys ' . \implode(' -> ', $keys) . ' could not be found.');
        }
        $curData = &$curData[$key];
    }

    $lastKey = $keys[count($keys) - 1];
    $curData[$lastKey] = $update($curData[$lastKey] ?? null);

    return $data;
}

function assign($obj, iterable $data) {
    foreach ($data as $key => $value) {
        $obj->{$key} = $value;
    }
    return $obj;
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
    return \Krak\Fn\slice(0, $iter, $num);
}

function drop(int $num, iterable $iter): iterable {
    return \Krak\Fn\slice($num, $iter);
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
    case '.':
        return $a . $b;
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

function zip(iterable ...$iters): \Iterator {
    if (count($iters) == 0) {
        return;
    }

    $iters = \array_map(iter::class, $iters);

    while (true) {
        $tup = [];
        foreach ($iters as $iter) {
            if (!$iter->valid()) {
                return;
            }
            $tup[] = $iter->current();
            $iter->next();
        }
        yield $tup;
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

function within(array $fields, iterable $iter): \Iterator {
    return \Krak\Fn\filterKeys(\Krak\Fn\Curried\inArray($fields), $iter);
}
function without(array $fields, iterable $iter): \Iterator {
    return \Krak\Fn\filterKeys(\Krak\Fn\Curried\not(\Krak\Fn\Curried\inArray($fields)), $iter);
}


// ALIASES

function inArray(array $set, $item): bool {
    return \in_array($item, $set);
}

function arrayMap(callable $fn, array $data): array {
    return \array_map($fn, $data);
}

function arrayFilter(callable $fn, array $data): array {
    return \array_filter($data, $fn);
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
    $parts = \array_fill(0, $numParts, []);
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

function mapKeyValue(callable $fn , iterable $iter) {
    foreach ($iter as $key => $value) {
        [$key, $value] = $fn([$key, $value]);
        yield $key => $value;
    }
}

function mapOn(array $maps, iterable $iter): iterable {
    foreach ($iter as $key => $value) {
        if (isset($maps[$key])) {
            yield $key => $maps[$key]($value);
        } else {
            yield $key => $value;
        }
    }
}

function reduce(callable $reduce, iterable $iter, $acc = null) {
    foreach ($iter as $key => $value) {
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
    foreach ($iter as $key => $value) {
        if ($predicate($key)) {
            yield $key => $value;
        }
    }
}

function values(iterable $iter): \Iterator {
    foreach ($iter as $v) {
        yield $v;
    }
}

function keys(iterable $iter): \Iterator {
    foreach ($iter as $k => $v) {
        yield $k;
    }
}

function flip(iterable $iter) {
    foreach ($iter as $k => $v) {
        yield $v => $k;
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
        list($appliedArgs, $args) = \array_reduce($appliedArgs, function($acc, $arg) {
            list($appliedArgs, $args) = $acc;
            if ($arg === \Krak\Fn\placeholder()) {
                $arg = array_shift($args);
            }

            $appliedArgs[] = $arg;
            return [$appliedArgs, $args];
        }, [[], $args]);

        return $fn(...$appliedArgs, ...$args);
    };
}

function autoCurry(array $args, $numArgs, callable $fn) {
    if (\count($args) >= $numArgs) {
        return $fn(...$args);
    }
    if (\count($args) == $numArgs - 1) {
        return \Krak\Fn\partial($fn, ...$args);
    }
    if (\count($args) == 0) {
        return \Krak\Fn\curry($fn, $numArgs - 1);
    }

    return \Krak\Fn\curry(
        \Krak\Fn\partial($fn, ...$args),
        ($numArgs - 1 - \count($args))
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


// UTILITY

function retry(callable $fn, $shouldRetry = null) {
    if (\is_null($shouldRetry)) {
        $shouldRetry = function($numRetries, \Throwable $t = null) { return true; };
    }
    if (\is_int($shouldRetry)) {
        $maxTries = $shouldRetry;
        if ($maxTries < 0) {
            throw new \LogicException("maxTries must be greater than or equal to 0");
        }
        $shouldRetry = function($numRetries, \Throwable $t = null) use ($maxTries) { return $numRetries <= $maxTries; };
    }
    if (!\is_callable($shouldRetry)) {
        throw new \InvalidArgumentException('shouldRetry must be an int or callable');
    }


    $numRetries = 0;
    $t = null;
    while ($shouldRetry($numRetries, $t)) {
        try {
            return $fn();
        } catch (\Throwable $t) {}
        $numRetries += 1;
    }

    throw $t;
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
    return \Krak\Fn\pipe(...\array_reverse($fns));
}

function stack(array $funcs, callable $last = null, callable $resolve = null) {
    return function(...$args) use ($funcs, $resolve, $last) {
        return \Krak\Fn\reduce(function($acc, $func) use ($resolve) {
            return function(...$args) use ($acc, $func, $resolve) {
                $args[] = $acc;
                $func = $resolve ? $resolve($func) : $func;
                return $func(...$args);
            };
        }, $funcs, $last ?: function() { throw new \LogicException('No stack handler was able to capture this request'); });
    };
}

function each(callable $handle, iterable $iter) {
    foreach ($iter as $v) {
        $handle($v);
    }
}
/** @deprecated */
function onEach(callable $handle, iterable $iter) {
    foreach ($iter as $v) {
        $handle($v);
    }
}

function iter($iter): \Iterator {
    if (\is_array($iter)) {
        return new \ArrayIterator($iter);
    } else if ($iter instanceof \Iterator) {
        return $iter;
    } else if (\is_object($iter) || \is_iterable($iter)) {
        return (function($iter) {
            foreach ($iter as $key => $value) {
                yield $key => $value;
            }
        })($iter);
    } else if (\is_string($iter)) {
        return (function($s) {
            for ($i = 0; $i < \strlen($s); $i++) {
                yield $i => $s[$i];
            }
        })($iter);
    }

    throw new \LogicException('Iter could not be converted into an iterable.');
}
