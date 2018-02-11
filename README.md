# Fn

Yet another functional library for PHP. What makes this library special is that it uses PHP Parser to generate curried versions of the non-curried implementations for best performance.

## Installation

Install with composer at `krak/fn`

## Usage

All functions are defined in `Krak\Fn`, are not curried, and are data last. Curried versions of functions are defined `Kran\Fn\Curried`. Constants are also generated per function in `Krak\Fn`.

```php
<?php

use function Krak\Fn\{compose};
use function Krak\Fn\Curried\{filter, map, op};
use const Krak\Fn\{toArray};

$res = compose(
    toArray,
    map(op('*')(3)),
    filter(op('>')(2))
)([1,2,3,4]);
assert($res == [9, 12]);
```

Check the `src/fn.php` for examples of all the functions.

### Constants

All functions have equivalent constants generated. These constants are defined as the fully qualified name of the function.

```php
namespace Krak\Fn {
    function toArray($data);
    const toArray = 'Krak\\Fn\\toArray';
};
```

With the example of `toArray`, you can call `toArray` on the data, or if you want to use compose, you can use the constant to reference the function.

```php
use function Krak\Fn\compose;
use const Krak\Fn\toArray;
compose(toArray, [1,2,3]);
```

Another great example is partial application.

```php
use function Krak\Fn\{partial, map, toArray};
use const Krak\Fn\{op};

$res = toArray(map(partial(op, '*', 3)), [1,2,3]);
assert($res == [3,6,9]);
```

The `op` function is defined as `op($operator, $b, $a)`. Essentially, what we did was call: `partial('Krak\\Fn\\op', '*', 3)`.

### Currying

All functions that are curryable have generated curry functions. A function is curryable if it has more than one required argument or one required argument with any number of optional arguments.

These function definitions aren't curryable:

```php
func()
func($arg1)
func($oarg = null, $oarg1 = null)
```

These are:

```php
func($arg1, $arg2)
func($arg1, $oarg = null)
```

Given a function definition like:

```
(a, b, c = null) -> Void
```

the curried verison would look like:

```
(a, c = null) -> (b) -> Void
```

## API
<table><tr><td><a href="#api-krak-fn-curry">curry</a></td><td><a href="#api-krak-fn-partial">partial</a></td><td><a href="#api-krak-fn-toarray">toArray</a></td><td><a href="#api-krak-fn-toarraywithkeys">toArrayWithKeys</a></td></tr><tr><td><a href="#api-krak-fn-partition">partition</a></td><td><a href="#api-krak-fn-filter">filter</a></td><td><a href="#api-krak-fn-map">map</a></td></tr></table>

<h3 id="api-krak-fn-curry">curry(callable $fn, $num = 1)</h3>

**Name:** `Krak\Fn\curry`

currys the given function $n times:

```php
$res = curry(_idArgs::class, 2)(1)(2)(3);
expect($res)->equal([1, 2, 3]);
```

Given a function definition: (a, b) -> c. A curried version will look like (a) -> (b) -> c

<h3 id="api-krak-fn-partial">partial(callable $fn, ...$appliedArgs)</h3>

**Name:** `Krak\Fn\partial`

Partially applies arguments to a function. Given a function signature like f = (a, b, c) -> d, partial(f, a, b) -> (c) -> d:

```php
$fn = function ($a, $b, $c) {
    return ($a + $b) * $c;
};
$fn = partial($fn, 1, 2);
// apply the two arguments (a, b) and return a new function with signature (c) -> d
expect($fn(3))->equal(9);
```

You can also use place holders when partially applying:

```php
$fn = function ($a, $b, $c) {
    return ($a + $b) * $c;
};
// _() represents a placeholder for parameter b.
$fn = partial($fn, 1, _(), 3);
// create the new func with signature (b) -> d
expect($fn(2))->equal(9);
```

Full partial application also works:

```php
$fn = function ($a, $b) {
    return [$a, $b];
};
$fn = partial($fn, 1, 2);
expect($fn())->equal([1, 2]);
```



<h3 id="api-krak-fn-toarray">toArray($iter)</h3>

**Name:** `Krak\Fn\toArray`

will tranform any iterable into an array:

```php
$res = toArray((function () {
    (yield 1);
    (yield 2);
    (yield 3);
})());
expect($res)->equal([1, 2, 3]);
```

can also be used as a constant:

```php
$res = compose(toArray, id)((function () {
    (yield 1);
    (yield 2);
    (yield 3);
})());
expect($res)->equal([1, 2, 3]);
```



<h3 id="api-krak-fn-toarraywithkeys">toArrayWithKeys($iter)</h3>

**Name:** `Krak\Fn\toArrayWithKeys`

can convert to an array and keep the keys:

```php
$gen = function () {
    (yield 'a' => 1);
    (yield 'b' => 2);
};
expect(toArrayWithKeys($gen()))->equal(['a' => 1, 'b' => 2]);
```



<h3 id="api-krak-fn-partition">partition(callable $partition, $data, $numParts = 2)</h3>

**Name:** `Krak\Fn\partition`

Splits an iterable into different arrays based off of a predicate. The predicate should return the index to partition the data into:

```php
list($left, $right) = partition(function ($v) {
    return $v < 3 ? 0 : 1;
}, [1, 2, 3, 4]);
expect([$left, $right])->equal([[1, 2], [3, 4]]);
```



<h3 id="api-krak-fn-filter">filter(callable $predicate, $data)</h3>

**Name:** `Krak\Fn\filter`

Filters an iterable off of a predicate that should return true or false. If true, keep the data, else remove the data from the iterable:

```php
$values = toArray(filter(partial(op, '>', 2), [1, 2, 3, 4]));
// keep all items that are greater than 2
expect($values)->equal([3, 4]);
```



<h3 id="api-krak-fn-map">map(callable $predicate, $data)</h3>

**Name:** `Krak\Fn\map`


