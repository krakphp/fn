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
        return property_exists($key, $data) ? $data->{$key} : $else;
    };
}
function index($key, $else = null)
{
    return function (array $data) use($key, $else) {
        return array_key_exists($key, $data) ? $data[$key] : $else;
    };
}
function propIn(array $keys, $else = null)
{
    return function ($data) use($keys, $else) {
        foreach ($props as $prop) {
            if (!is_object($obj) || !isset($obj->{$prop})) {
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
            if (!is_array($data) || !array_key_exists($part, $data)) {
                return $else;
            }
            $data = $data[$part];
        }
        return $data;
    };
}
function without(array $fields)
{
    return function ($data) use($fields) {
        return fromPairs(filter(function ($tup) use($fields) {
            return !\in_array($tup[0], $fields);
        }, toPairs($data)));
    };
}
function op($op)
{
    return function ($b) use($op) {
        return function ($a) use($b, $op) {
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
                    throw new \LogicException('Invalid operator ' . $op);
            }
        };
    };
}
function inArray(array $set)
{
    return function ($item) use($set) {
        return \in_array($item, $set);
    };
}
function all(callable $predicate)
{
    return function ($data) use($predicate) {
        foreach ($data as $key => $value) {
            if (!$predicate($value)) {
                return false;
            }
        }
        return true;
    };
}
function any(callable $predicate)
{
    return function ($data) use($predicate) {
        foreach ($data as $key => $value) {
            if ($predicate($value)) {
                return true;
            }
        }
        return false;
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
function partition(callable $partition, $numParts = 2)
{
    return function ($data) use($partition, $numParts) {
        $parts = array_fill(0, $numParts, []);
        foreach ($data as $val) {
            $index = (int) $partition($val);
            $parts[$index][] = $val;
        }
        return $parts;
    };
}
function search(callable $predicate)
{
    return function ($data) use($predicate) {
        foreach ($data as $value) {
            if ($predicate($value)) {
                return $value;
            }
        }
    };
}
function map(callable $predicate)
{
    return function ($data) use($predicate) {
        foreach ($data as $key => $value) {
            (yield $key => $predicate($value));
        }
    };
}
function mapKeys(callable $predicate)
{
    return function ($data) use($predicate) {
        foreach ($data as $key => $value) {
            (yield $predicate($key) => $value);
        }
    };
}
function reduce(callable $reduce, $acc = null)
{
    return function ($data) use($reduce, $acc) {
        foreach ($data as $key => $value) {
            $acc = $reduce($acc, $value);
        }
        return $acc;
    };
}
function filter(callable $predicate)
{
    return function ($data) use($predicate) {
        foreach ($data as $key => $value) {
            if ($predicate($value)) {
                (yield $key => $value);
            }
        }
    };
}
function filterKeys(callable $predicate)
{
    return function ($data) use($predicate) {
        foreach ($data as $key => $value) {
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
            list($appliedArgs, $args) = array_reduce($appliedArgs, function ($acc, $arg) {
                list($appliedArgs, $args) = $acc;
                if ($arg === placeholder()) {
                    $arg = array_shift($args);
                }
                $appliedArgs[] = $arg;
                return [$appliedArgs, $args];
            }, [[], $args]);
            return $fn(...$appliedArgs, ...$args);
        };
    };
}
function stack(callable $last = null, callable $resolve = null)
{
    return function ($funcs) use($last, $resolve) {
        return function (...$args) use($funcs, $resolve, $last) {
            return reduce(function ($acc, $func) use($resolve) {
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