<?php

/* This file is was automatically generated. */
namespace Krak\Fn\Curried;

function method($name, ...$optionalArgs)
{
    return function ($data) use($name, $optionalArgs) {
        return $data->{$name}(...$optionalArgs);
    };
}
function prop(string $key, $else = null)
{
    return function ($data) use($key, $else) {
        return \property_exists($data, $key) ? $data->{$key} : $else;
    };
}
function index($key, $else = null)
{
    return function (array $data) use($key, $else) {
        return \array_key_exists($key, $data) ? $data[$key] : $else;
    };
}
function propIn(array $props, $else = null)
{
    return function ($obj) use($props, $else) {
        foreach ($props as $prop) {
            if (!\is_object($obj) || !\property_exists($obj, $prop)) {
                return $else;
            }
            $obj = $obj->{$prop};
        }
        return $obj;
    };
}
function indexIn(array $keys, $else = null)
{
    return function (array $data) use($keys, $else) {
        foreach ($keys as $part) {
            if (!\is_array($data) || !\array_key_exists($part, $data)) {
                return $else;
            }
            $data = $data[$part];
        }
        return $data;
    };
}
function hasIndexIn(array $keys)
{
    return function (array $data) use($keys) {
        foreach ($keys as $key) {
            if (!\is_array($data) || !\array_key_exists($key, $data)) {
                return false;
            }
            $data = $data[$key];
        }
        return true;
    };
}
function updateIndexIn(array $keys)
{
    return function (callable $update) use($keys) {
        return function (array $data) use($update, $keys) {
            $curData =& $data;
            foreach (\array_slice($keys, 0, -1) as $key) {
                if (!\array_key_exists($key, $curData)) {
                    throw new \RuntimeException('Could not updateIn because the keys ' . \implode(' -> ', $keys) . ' could not be found.');
                }
                $curData =& $curData[$key];
            }
            $lastKey = $keys[count($keys) - 1];
            $curData[$lastKey] = $update($curData[$lastKey] ?? null);
            return $data;
        };
    };
}
function assign($obj)
{
    return function (iterable $data) use($obj) {
        foreach ($data as $key => $value) {
            $obj->{$key} = $value;
        }
        return $obj;
    };
}
function takeWhile(callable $predicate)
{
    return function (iterable $iter) use($predicate) {
        foreach ($iter as $k => $v) {
            if ($predicate($v)) {
                (yield $k => $v);
            } else {
                return;
            }
        }
    };
}
function dropWhile(callable $predicate)
{
    return function (iterable $iter) use($predicate) {
        $stillDropping = true;
        foreach ($iter as $k => $v) {
            if ($stillDropping && $predicate($v)) {
                continue;
            } else {
                if ($stillDropping) {
                    $stillDropping = false;
                }
            }
            (yield $k => $v);
        }
    };
}
function take(int $num)
{
    return function (iterable $iter) use($num) {
        return \Krak\Fn\slice(0, $iter, $num);
    };
}
function drop(int $num)
{
    return function (iterable $iter) use($num) {
        return \Krak\Fn\slice($num, $iter);
    };
}
function slice(int $start, $length = INF)
{
    return function (iterable $iter) use($start, $length) {
        assert($start >= 0);
        $i = 0;
        $end = $start + $length - 1;
        foreach ($iter as $k => $v) {
            if ($start <= $i && $i <= $end) {
                (yield $k => $v);
            }
            $i += 1;
        }
    };
}
function chunk(int $size)
{
    return function (iterable $iter) use($size) {
        assert($size > 0);
        $chunk = [];
        foreach ($iter as $v) {
            $chunk[] = $v;
            if (\count($chunk) == $size) {
                (yield $chunk);
                $chunk = [];
            }
        }
        if ($chunk) {
            (yield $chunk);
        }
    };
}
function range($start, $step = null)
{
    return function ($end) use($start, $step) {
        if ($start == $end) {
            (yield $start);
        } else {
            if ($start < $end) {
                $step = $step ?: 1;
                if ($step <= 0) {
                    throw new \InvalidArgumentException('Step must be greater than 0.');
                }
                for ($i = $start; $i <= $end; $i += $step) {
                    (yield $i);
                }
            } else {
                $step = $step ?: -1;
                if ($step >= 0) {
                    throw new \InvalidArgumentException('Step must be less than 0.');
                }
                for ($i = $start; $i >= $end; $i += $step) {
                    (yield $i);
                }
            }
        }
    };
}
function op(string $op)
{
    return function ($b) use($op) {
        return function ($a) use($b, $op) {
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
                    throw new \LogicException('Invalid operator ' . $op);
            }
        };
    };
}
function flatMap(callable $map)
{
    return function (iterable $iter) use($map) {
        foreach ($iter as $k => $v) {
            foreach ($map($v) as $k => $v) {
                (yield $k => $v);
            }
        }
    };
}
function flatten($levels = INF)
{
    return function (iterable $iter) use($levels) {
        if ($levels == 0) {
            return $iter;
        } else {
            if ($levels == 1) {
                foreach ($iter as $k => $v) {
                    if (\is_iterable($v)) {
                        foreach ($v as $k1 => $v1) {
                            (yield $k1 => $v1);
                        }
                    } else {
                        (yield $k => $v);
                    }
                }
            } else {
                foreach ($iter as $k => $v) {
                    if (\is_iterable($v)) {
                        foreach (flatten($v, $levels - 1) as $k1 => $v1) {
                            (yield $k1 => $v1);
                        }
                    } else {
                        (yield $k => $v);
                    }
                }
            }
        }
    };
}
function when(callable $if)
{
    return function (callable $then) use($if) {
        return function ($value) use($then, $if) {
            return $if($value) ? $then($value) : $value;
        };
    };
}
function within(array $fields)
{
    return function (iterable $iter) use($fields) {
        return \Krak\Fn\filterKeys(\Krak\Fn\Curried\inArray($fields), $iter);
    };
}
function without(array $fields)
{
    return function (iterable $iter) use($fields) {
        return \Krak\Fn\filterKeys(\Krak\Fn\Curried\not(\Krak\Fn\Curried\inArray($fields)), $iter);
    };
}
function inArray(array $set)
{
    return function ($item) use($set) {
        return \in_array($item, $set);
    };
}
function arrayMap(callable $fn)
{
    return function (array $data) use($fn) {
        return \array_map($fn, $data);
    };
}
function arrayFilter(callable $fn)
{
    return function (array $data) use($fn) {
        return \array_filter($data, $fn);
    };
}
function all(callable $predicate)
{
    return function (iterable $iter) use($predicate) {
        foreach ($iter as $key => $value) {
            if (!$predicate($value)) {
                return false;
            }
        }
        return true;
    };
}
function any(callable $predicate)
{
    return function (iterable $iter) use($predicate) {
        foreach ($iter as $key => $value) {
            if ($predicate($value)) {
                return true;
            }
        }
        return false;
    };
}
function search(callable $predicate)
{
    return function (iterable $iter) use($predicate) {
        foreach ($iter as $value) {
            if ($predicate($value)) {
                return $value;
            }
        }
    };
}
function indexOf(callable $predicate)
{
    return function (iterable $iter) use($predicate) {
        foreach ($iter as $key => $value) {
            if ($predicate($value)) {
                return $key;
            }
        }
    };
}
function trans(callable $trans)
{
    return function (callable $fn) use($trans) {
        return function ($data) use($fn, $trans) {
            return $fn($trans($data));
        };
    };
}
function not(callable $fn)
{
    return function (...$args) use($fn) {
        return !$fn(...$args);
    };
}
function isInstance($class)
{
    return function ($item) use($class) {
        return $item instanceof $class;
    };
}
function partition(callable $partition, int $numParts = 2)
{
    return function (iterable $iter) use($partition, $numParts) {
        $parts = \array_fill(0, $numParts, []);
        foreach ($iter as $val) {
            $index = (int) $partition($val);
            $parts[$index][] = $val;
        }
        return $parts;
    };
}
function map(callable $predicate)
{
    return function (iterable $iter) use($predicate) {
        foreach ($iter as $key => $value) {
            (yield $key => $predicate($value));
        }
    };
}
function mapKeys(callable $predicate)
{
    return function (iterable $iter) use($predicate) {
        foreach ($iter as $key => $value) {
            (yield $predicate($key) => $value);
        }
    };
}
function mapKeyValue(callable $fn)
{
    return function (iterable $iter) use($fn) {
        foreach ($iter as $key => $value) {
            [$key, $value] = $fn([$key, $value]);
            (yield $key => $value);
        }
    };
}
function mapOn(array $maps)
{
    return function (iterable $iter) use($maps) {
        foreach ($iter as $key => $value) {
            if (isset($maps[$key])) {
                (yield $key => $maps[$key]($value));
            } else {
                (yield $key => $value);
            }
        }
    };
}
function reduce(callable $reduce, $acc = null)
{
    return function (iterable $iter) use($reduce, $acc) {
        foreach ($iter as $key => $value) {
            $acc = $reduce($acc, $value);
        }
        return $acc;
    };
}
function filter(callable $predicate)
{
    return function (iterable $iter) use($predicate) {
        foreach ($iter as $key => $value) {
            if ($predicate($value)) {
                (yield $key => $value);
            }
        }
    };
}
function filterKeys(callable $predicate)
{
    return function (iterable $iter) use($predicate) {
        foreach ($iter as $key => $value) {
            if ($predicate($key)) {
                (yield $key => $value);
            }
        }
    };
}
function partial(callable $fn)
{
    return function (...$appliedArgs) use($fn) {
        return function (...$args) use($fn, $appliedArgs) {
            list($appliedArgs, $args) = \array_reduce($appliedArgs, function ($acc, $arg) {
                list($appliedArgs, $args) = $acc;
                if ($arg === \Krak\Fn\placeholder()) {
                    $arg = array_shift($args);
                }
                $appliedArgs[] = $arg;
                return [$appliedArgs, $args];
            }, [[], $args]);
            return $fn(...$appliedArgs, ...$args);
        };
    };
}
function retry($shouldRetry = null)
{
    return function (callable $fn) use($shouldRetry) {
        if (\is_null($shouldRetry)) {
            $shouldRetry = function ($numRetries, \Throwable $t = null) {
                return true;
            };
        }
        if (\is_int($shouldRetry)) {
            $maxTries = $shouldRetry;
            if ($maxTries < 0) {
                throw new \LogicException("maxTries must be greater than or equal to 0");
            }
            $shouldRetry = function ($numRetries, \Throwable $t = null) use($maxTries) {
                return $numRetries <= $maxTries;
            };
        }
        if (!\is_callable($shouldRetry)) {
            throw new \InvalidArgumentException('shouldRetry must be an int or callable');
        }
        $numRetries = 0;
        $t = null;
        while ($shouldRetry($numRetries, $t)) {
            try {
                return $fn();
            } catch (\Throwable $t) {
            }
            $numRetries += 1;
        }
        throw $t;
    };
}
function stack(callable $last = null, callable $resolve = null)
{
    return function (array $funcs) use($last, $resolve) {
        return function (...$args) use($funcs, $resolve, $last) {
            return \Krak\Fn\reduce(function ($acc, $func) use($resolve) {
                return function (...$args) use($acc, $func, $resolve) {
                    $args[] = $acc;
                    $func = $resolve ? $resolve($func) : $func;
                    return $func(...$args);
                };
            }, $funcs, $last ?: function () {
                throw new \LogicException('No stack handler was able to capture this request');
            });
        };
    };
}
function each(callable $handle)
{
    return function (iterable $iter) use($handle) {
        foreach ($iter as $v) {
            $handle($v);
        }
    };
}
function onEach(callable $handle)
{
    return function (iterable $iter) use($handle) {
        foreach ($iter as $v) {
            $handle($v);
        }
    };
}