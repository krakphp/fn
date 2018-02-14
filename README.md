# Fn

Yet another functional library for PHP. What makes this library special is that it uses PHP Parser to generate curried versions of the non-curried implementations for best performance.

## Installation

Install with composer at `krak/fn`

## Usage

All functions are defined in `Krak\Fn`, are not curried, and are data last. Curried versions of functions are defined `Kran\Fn\Curried`. Constants are also generated per function in `Krak\Fn`.

```php
<?php

use function Krak\Fn\Curried\{compose, filter, map, op};
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
<table><tr><td><a href="#api-krak-fn-curry">curry</a></td><td><a href="#api-krak-fn-partial">partial</a></td><td><a href="#api-krak-fn-toarray">toArray</a></td><td><a href="#api-krak-fn-toarraywithkeys">toArrayWithKeys</a></td><td><a href="#api-krak-fn-partition">partition</a></td><td><a href="#api-krak-fn-filter">filter</a></td><td><a href="#api-krak-fn-map">map</a></td><td><a href="#api-krak-fn-inarray">inArray</a></td></tr><tr><td><a href="#api-krak-fn-when">when</a></td><td><a href="#api-krak-fn-head">head</a></td><td><a href="#api-krak-fn-topairs">toPairs</a></td><td><a href="#api-krak-fn-frompairs">fromPairs</a></td><td><a href="#api-krak-fn-range">range</a></td><td><a href="#api-krak-fn-slice">slice</a></td><td><a href="#api-krak-fn-take">take</a></td><td><a href="#api-krak-fn-takewhile">takeWhile</a></td></tr><tr><td><a href="#api-krak-fn-drop">drop</a></td><td><a href="#api-krak-fn-dropwhile">dropWhile</a></td><td><a href="#api-krak-fn-op">op</a></td><td><a href="#api-krak-fn-chunk">chunk</a></td><td><a href="#api-krak-fn-index">index</a></td><td><a href="#api-krak-fn-indexin">indexIn</a></td><td><a href="#api-krak-fn-flatten">flatten</a></td><td><a href="#api-krak-fn-chain">chain</a></td></tr><tr><td><a href="#api-krak-fn-oneach">onEach</a></td></tr></table>

<h3 id="api-krak-fn-curry">curry(callable $fn, int $num = 1)</h3>

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



<h3 id="api-krak-fn-toarray">toArray(iterable $iter): array</h3>

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



<h3 id="api-krak-fn-toarraywithkeys">toArrayWithKeys(iterable $iter): array</h3>

**Name:** `Krak\Fn\toArrayWithKeys`

can convert to an array and keep the keys:

```php
$gen = function () {
    (yield 'a' => 1);
    (yield 'b' => 2);
};
expect(toArrayWithKeys($gen()))->equal(['a' => 1, 'b' => 2]);
```



<h3 id="api-krak-fn-partition">partition(callable $partition, iterable $iter, int $numParts = 2): array</h3>

**Name:** `Krak\Fn\partition`

Splits an iterable into different arrays based off of a predicate. The predicate should return the index to partition the data into:

```php
list($left, $right) = partition(function ($v) {
    return $v < 3 ? 0 : 1;
}, [1, 2, 3, 4]);
expect([$left, $right])->equal([[1, 2], [3, 4]]);
```



<h3 id="api-krak-fn-filter">filter(callable $predicate, iterable $iter): iterable</h3>

**Name:** `Krak\Fn\filter`

Lazily filters an iterable off of a predicate that should return true or false. If true, keep the data, else remove the data from the iterable:

```php
$values = filter(partial(op, '>', 2), [1, 2, 3, 4]);
// keep all items that are greater than 2
expect(toArray($values))->equal([3, 4]);
```



<h3 id="api-krak-fn-map">map(callable $predicate, iterable $iter): iterable</h3>

**Name:** `Krak\Fn\map`

Lazily maps an iterable's values to a different set:

```php
$values = map(partial(op, '*', 2), [1, 2, 3, 4]);
expect(toArray($values))->equal([2, 4, 6, 8]);
```



<h3 id="api-krak-fn-inarray">inArray(array $set, $item): bool</h3>

**Name:** `Krak\Fn\inArray`

Checks if an item is within an array of items:

```php
$res = inArray([1, 2, 3], 2);
expect($res)->equal(true);
```



<h3 id="api-krak-fn-when">when(callable $if, callable $then, $value)</h3>

**Name:** `Krak\Fn\when`

Evaluates the given value with the $then callable if the predicate returns true:

```php
$if = function ($v) {
    return $v == 3;
};
$then = function ($v) {
    return $v * $v;
};
$res = when($if, $then, 3);
expect($res)->equal(9);
```

But will return the given value if the predicate returns false:

```php
$if = function ($v) {
    return $v == 3;
};
$then = function ($v) {
    return $v * $v;
};
$res = when($if, $then, 4);
expect($res)->equal(4);
```



<h3 id="api-krak-fn-head">head(iterable $iter)</h3>

**Name:** `Krak\Fn\head`

Returns the fist element in an iterable:

```php
$res = head([1, 2, 3]);
expect($res)->equal(1);
```

But returns null if the iterable is empty:

```php
$res = head([]);
expect($res)->equal(null);
```



<h3 id="api-krak-fn-topairs">toPairs(iterable $iter): iterable</h3>

**Name:** `Krak\Fn\toPairs`

Transforms an associative array into an iterable of tuples [$key, $value]:

```php
$res = toPairs(['a' => 1, 'b' => 2]);
expect(toArray($res))->equal([['a', 1], ['b', 2]]);
```



<h3 id="api-krak-fn-frompairs">fromPairs(iterable $iter): iterable</h3>

**Name:** `Krak\Fn\fromPairs`

Converts an iterable of tuples [$key, $value] into an associative iterable:

```php
$res = fromPairs([['a', 1], ['b', 2]]);
expect(toArrayWithKeys($res))->equal(['a' => 1, 'b' => 2]);
```



<h3 id="api-krak-fn-range">range($start, $end, $step = null)</h3>

**Name:** `Krak\Fn\range`

Creates an iterable of a range of values starting from $start going to $end inclusively incrementing by $step:

```php
$res = range(1, 3);
expect(toArray($res))->equal([1, 2, 3]);
```

It also allows a decreasing range:

```php
$res = range(3, 1);
expect(toArray($res))->equal([3, 2, 1]);
```

An exception will be thrown if the $step provided goes in the wrong direction:

```php
expect(function () {
    toArray(range(1, 2, -1));
})->throw(\InvalidArgumentException::class);
expect(function () {
    toArray(range(2, 1, 1));
})->throw(\InvalidArgumentException::class);
```



<h3 id="api-krak-fn-slice">slice(int $start, iterable $iter, $length = INF): iterable</h3>

**Name:** `Krak\Fn\slice`

It takes an inclusive slice from start to a given length of an interable:

```php
$sliced = slice(1, range(0, 4), 2);
expect(toArray($sliced))->equal([1, 2]);
```

If length is not supplied it default to the end of the iterable:

```php
$sliced = slice(2, range(0, 4));
expect(toArray($sliced))->equal([2, 3, 4]);
```



<h3 id="api-krak-fn-take">take(int $num, iterable $iter): iterable</h3>

**Name:** `Krak\Fn\take`

Takes the first num items from an iterable:

```php
$res = take(2, range(0, 10));
expect(toArray($res))->equal([0, 1]);
```



<h3 id="api-krak-fn-takewhile">takeWhile(callable $predicate, iterable $iter): iterable</h3>

**Name:** `Krak\Fn\takeWhile`

Takes elements from an iterable while the $predicate returns true:

```php
$res = takeWhile(Curried\op('>')(0), [2, 1, 0, 1, 2]);
expect(toArray($res))->equal([2, 1]);
```



<h3 id="api-krak-fn-drop">drop(int $num, iterable $iter): iterable</h3>

**Name:** `Krak\Fn\drop`

Drops the first num items from an iterable:

```php
$res = drop(2, range(0, 3));
expect(toArray($res))->equal([2, 3]);
```



<h3 id="api-krak-fn-dropwhile">dropWhile(callable $predicate, iterable $iter): iterable</h3>

**Name:** `Krak\Fn\dropWhile`

Drops elements from the iterable while the predicate returns true:

```php
$res = dropWhile(Curried\op('>')(0), [2, 1, 0, 1, 2]);
expect(toArray($res))->equal([0, 1, 2]);
```



<h3 id="api-krak-fn-op">op(string $op, $b, $a)</h3>

**Name:** `Krak\Fn\op`

op evaluates binary operations. It expects the right hand operator first which makes most sense when currying or partially applying the op function.
When reading the op func, it should be read: `evaluate $op with $b with $a` e.g.:

```
op('+', 2, 3) -> add 2 with 3
op('-', 2, 3) -> subtract 2 from 3
op('>', 2, 3) => compare greater than 2 with 3
```

Evaluates two values with a given operator:

```php
$res = op('<', 2, 1);
expect($res)->equal(true);
```

Supports equality operators:

```php
$obj = new stdClass();
$ops = [['==', [1, 1]], ['eq', [2, 2]], ['!=', [1, 2]], ['neq', [2, 3]], ['===', [$obj, $obj]], ['!==', [new stdClass(), new stdClass()]], ['>', [1, 2]], ['gt', [1, 3]], ['>=', [1, 2]], ['gte', [1, 1]], ['<', [2, 1]], ['lt', [3, 1]], ['<=', [2, 1]], ['lte', [1, 1]]];
foreach ($ops as list($op, list($b, $a))) {
    $res = op($op, $b, $a);
    expect($res)->equal(true);
}
```

Supports arithmetic operators:

```php
$ops = [['+', [2, 3], 5], ['-', [2, 3], 1], ['*', [2, 3], 6], ['**', [2, 3], 9], ['/', [2, 3], 1.5], ['%', [2, 3], 1]];
foreach ($ops as list($op, list($b, $a), $expected)) {
    $res = op($op, $b, $a);
    expect($res)->equal($expected);
}
```

Is more useful partially applied or curried:

```php
$add2 = Curried\op('+')(2);
$mul3 = partial(op, '*', 3);
$sub4 = Curried\op('-')(4);
// ((2 + 2) * 3) - 4
$res = compose($sub4, $mul3, $add2)(2);
expect($res)->equal(8);
```



<h3 id="api-krak-fn-chunk">chunk(int $size, iterable $iter): iterable</h3>

**Name:** `Krak\Fn\chunk`

Chunks an iterable into equal sized chunks.:

```php
$res = chunk(2, [1, 2, 3, 4]);
expect(toArray($res))->equal([[1, 2], [3, 4]]);
```

If there is any remainder, it is yielded as is:

```php
$res = chunk(3, [1, 2, 3, 4]);
expect(toArray($res))->equal([[1, 2, 3], [4]]);
```



<h3 id="api-krak-fn-index">index($key, array $data, $else = null)</h3>

**Name:** `Krak\Fn\index`

Accesses an index in an array:

```php
$res = index('a', ['a' => 1]);
expect($res)->equal(1);
```

If no value exists at the given index, $else will be returned:

```php
$res = index('a', ['b' => 1], 2);
expect($res)->equal(2);
```



<h3 id="api-krak-fn-indexin">indexIn(array $keys, array $data, $else = null)</h3>

**Name:** `Krak\Fn\indexIn`

Accesses a nested index in a deep array structure:

```php
$res = indexIn(['a', 'b'], ['a' => ['b' => 1]]);
expect($res)->equal(1);
```

If any of the indexes do not exist, $else will be returned:

```php
$res = indexIn(['a', 'b'], ['a' => ['c' => 1]], 2);
expect($res)->equal(2);
```



<h3 id="api-krak-fn-flatten">flatten(iterable $iter, $levels = INF)</h3>

**Name:** `Krak\Fn\flatten`

Flattens nested iterables into a flattened set of elements:

```php
$res = flatten([1, [2, [3, [4]]]]);
expect(toArray($res))->equal([1, 2, 3, 4]);
```

Can flatten a specific number of levels:

```php
$res = flatten([1, [2, [3]]], 1);
expect(toArray($res))->equal([1, 2, [3]]);
```



<h3 id="api-krak-fn-chain">chain(iterable ...$iters)</h3>

**Name:** `Krak\Fn\chain`

Chains iterables together into one iterable:

```php
$res = chain([1], range(2, 3));
expect(toArray($res))->equal([1, 2, 3]);
```



<h3 id="api-krak-fn-oneach">onEach(callable $handle, iterable $iter)</h3>

**Name:** `Krak\Fn\onEach`

Invokes a callable on each item in an iterable:

```php
$state = [(object) ['id' => 1], (object) ['id' => 2]];
onEach(function ($item) {
    $item->id += 1;
}, $state);
expect([$state[0]->id, $state[1]->id])->equal([2, 3]);
```

Normally using php foreach should suffice for iterating over an iterable; however, php variables in foreach loops are not scoped whereas closures are.
