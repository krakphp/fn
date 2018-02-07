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

All functions that are curryable hav generated curry functions. A function is curryable if it has more than one required argument or one required argument with any number of optional arguments.

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
