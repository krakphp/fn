# Fun

Yet another functional library for PHP. What makes this library special is that it uses PHP Parser to generate curried versions of the non-curried implementations for best performance.

## Installation

Install with composer at `krak/fn`

## Usage

All functions are defined in `Krak\Fun`, are not curried, and are data last. Curried versions of functions are defined `Kran\Fun\Curried`. Constants are also generated per function in `Krak\Fun\Consts`.

```php
<?php

use function Krak\Fun\Curried\{filter, map, op};
use function Krak\Fun\{compose};
use const Krak\Fun\Consts\{toArray};

$res = compose(
    toArray,
    map(op('*')(3)),
    filter(op('>')(2))
)([1,2,3,4]);
assert($res == [9, 12]);
```

Check the `src/fn.php` for examples of all the functions.

### Fun API

In addition to importing the functions/consts individually, you can also utilize the `f` and `c` namespaces as a shorthand which make using the library a lot easier.

```php
<?php

use Krak\Fun\{f, c};

$res = f\compose(
    c\toArray,
    c\map(c\op('*')(3)),
    c\filter(c\op('>')(2))
)([1,2,3,4]);
assert($res == [9, 12]);
```

The `f` namespace holds the standard functions basically copied over verbatim from the `Krak\Fun` namespace.

The `c` namespace contains all of the curried functions and constant definitions.

One great way to use the consts is with compose or pipe chains:

```php
use Krak\Fun\{f, c};

$res = f\compose(
    c\toArray,
    c\map(function($tup) {
        return $tup[0] + $tup[1];
    }),
    c\toPairs
)([1,2,3]);
// $res == [1, 3, 5]
```

### Constants

This library generates constants with same name as the function they were generated from where their value is the fully qualified name of the function.

PHP (unfortunately) will treat strings as callables if they resolve to a function name. So generating constants with the same name as functions allows us to support a neat first class function type syntax.

```php
<?php

use Krak\Fun\{f, c};

function getArrayRange(callable $toArray): array {
    $toArray(f\range(1,3));
}

getArrayRange(c\toArray);
```

The above is valid php and will work because c\toArray resolves to `Krak\\Fun\\toArray` which php will treat as a valid callable.

This is great for compose chains and partial application:

```php
use Krak\Fun\{f, c};

$res = f\compose(
    c\toArray,
    map(partial(c\op, '*', 3))
)([1,2,3]);
assert($res == [3,6,9]);
```

The `op` function is defined as `op($operator, $b, $a)`. Essentially, what we did was call: `partial('Krak\\Fun\\op', '*', 3)`.

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


### Debugging

If you have a function compose chain and want to debug/test the result of any of the functions, you can do something like the following examples:

1. Debug a single value:

    ```php
    f\compose(
        function() {}, // do something else
        c\dd(), // debug result here
        function() {}, // do another thing that returns a single value
        function() {} // do something
    );
    ```
2. Debug an iterable:

    ```php
    f\compose(
        function() {}, // do something else
        c\dd(), c\toArray, // debug result here
        c\map(function() {}) // do something
    );
    ```

### Using Compose Chains for Readable Code

One of my favorite features of using this library is building compose chains in a way that make your application services a lot easier to read and follow along with.

```php

use Krak\Fun\{f, c};

/**
 * Fetches orders based off of the arguments, filters data, and imports to database
 */
final class ImportOrdersFromApi
{
    public function __construct(ApiClient $client, OrderRepository $orderRepository, SaveOrders $saveOrders) {
        // ...
    }

    public function __invoke(ImportOrderRequest $req): void {
        f\compose(
            $this->persistApiOrders(),
            $this->removeAlreadyImportedOrders(),
            $this->fetchOrdersFromApi()
        )($req);
    }

    private function persistApiOrders(): callable {
        // important that this is an c\each so that it will consume the iterable chain
        return f\compose(
            c\each($this->saveOrders), // saveOrders has a signature of `__invoke(iterable Order[]) => void`
            c\chunk(50), // chunk to persist many at once
            c\map(function(array $apiOrder) {
                return Order::createFromApiData($apiOrder);
            })
        );
    }

    private function removeAlreadyImportedOrders(): callable {
        return f\compose(
            c\flatMap(function(array $apiOrders) {
                $apiOrderIds = array_column($apiOrders, 'order_id');
                /** array of order id => order entity */
                $orders = $this->orderRepository->findByApiOrderIds($ids);
                return f\filter(function(array $apiOrder) use ($orders) {
                    return !array_key_exists($apiOrder['order_id'], $orders);
                }, $apiOrders);
            }),
            // chunk by 50 to save on database requests
            c\chunk(50)
        );
    }

    /** Returns an iterable of api orders */
    private function fetchOrdersFromApi(): callable {
        return function(ImportOrderRequest $req) {
            yield from $this->apiClient->fetch(/* pass in req args */);
        };
    }
}
```

## Docs

Docs are generated with `make docs`. This uses Krak Peridocs to actually generate the documentation from the peridot tests.

## Code Generation

The constants and curried functions are generated with `make code`.

## Tests

Tests are run via `make test` and are stored in the `test` directory. We use peridot for testing.

## API
<table><tr><td><a href="#api-krak-fun-all">all</a></td><td><a href="#api-krak-fun-any">any</a></td><td><a href="#api-krak-fun-arraycompact">arrayCompact</a></td><td><a href="#api-krak-fun-arrayfilter">arrayFilter</a></td><td><a href="#api-krak-fun-arraymap">arrayMap</a></td><td><a href="#api-krak-fun-arrayreindex">arrayReindex</a></td><td><a href="#api-krak-fun-assign">assign</a></td><td><a href="#api-krak-fun-chain">chain</a></td></tr><tr><td><a href="#api-krak-fun-chunk">chunk</a></td><td><a href="#api-krak-fun-chunkby">chunkBy</a></td><td><a href="#api-krak-fun-compact">compact</a></td><td><a href="#api-krak-fun-compose">compose</a></td><td><a href="#api-krak-fun-construct">construct</a></td><td><a href="#api-krak-fun-curry">curry</a></td><td><a href="#api-krak-fun-differencewith">differenceWith</a></td><td><a href="#api-krak-fun-dd">dd</a></td></tr><tr><td><a href="#api-krak-fun-drop">drop</a></td><td><a href="#api-krak-fun-dropwhile">dropWhile</a></td><td><a href="#api-krak-fun-each">each</a></td><td><a href="#api-krak-fun-filter">filter</a></td><td><a href="#api-krak-fun-filterkeys">filterKeys</a></td><td><a href="#api-krak-fun-flatmap">flatMap</a></td><td><a href="#api-krak-fun-flatten">flatten</a></td><td><a href="#api-krak-fun-flip">flip</a></td></tr><tr><td><a href="#api-krak-fun-frompairs">fromPairs</a></td><td><a href="#api-krak-fun-groupby">groupBy</a></td><td><a href="#api-krak-fun-hasindexin">hasIndexIn</a></td><td><a href="#api-krak-fun-head">head</a></td><td><a href="#api-krak-fun-inarray">inArray</a></td><td><a href="#api-krak-fun-index">index</a></td><td><a href="#api-krak-fun-indexin">indexIn</a></td><td><a href="#api-krak-fun-indexof">indexOf</a></td></tr><tr><td><a href="#api-krak-fun-isnull">isNull</a></td><td><a href="#api-krak-fun-iter">iter</a></td><td><a href="#api-krak-fun-join">join</a></td><td><a href="#api-krak-fun-keys">keys</a></td><td><a href="#api-krak-fun-map">map</a></td><td><a href="#api-krak-fun-mapaccum">mapAccum</a></td><td><a href="#api-krak-fun-mapkeys">mapKeys</a></td><td><a href="#api-krak-fun-mapkeyvalue">mapKeyValue</a></td></tr><tr><td><a href="#api-krak-fun-mapon">mapOn</a></td><td><a href="#api-krak-fun-nullable">nullable</a></td><td><a href="#api-krak-fun-oneach">onEach</a></td><td><a href="#api-krak-fun-op">op</a></td><td><a href="#api-krak-fun-pad">pad</a></td><td><a href="#api-krak-fun-partial">partial</a></td><td><a href="#api-krak-fun-partition">partition</a></td><td><a href="#api-krak-fun-pick">pick</a></td></tr><tr><td><a href="#api-krak-fun-pickby">pickBy</a></td><td><a href="#api-krak-fun-pipe">pipe</a></td><td><a href="#api-krak-fun-product">product</a></td><td><a href="#api-krak-fun-prop">prop</a></td><td><a href="#api-krak-fun-propin">propIn</a></td><td><a href="#api-krak-fun-range">range</a></td><td><a href="#api-krak-fun-reduce">reduce</a></td><td><a href="#api-krak-fun-reducekeyvalue">reduceKeyValue</a></td></tr><tr><td><a href="#api-krak-fun-reindex">reindex</a></td><td><a href="#api-krak-fun-retry">retry</a></td><td><a href="#api-krak-fun-search">search</a></td><td><a href="#api-krak-fun-setindex">setIndex</a></td><td><a href="#api-krak-fun-setindexin">setIndexIn</a></td><td><a href="#api-krak-fun-setprop">setProp</a></td><td><a href="#api-krak-fun-slice">slice</a></td><td><a href="#api-krak-fun-sortfromarray">sortFromArray</a></td></tr><tr><td><a href="#api-krak-fun-spread">spread</a></td><td><a href="#api-krak-fun-take">take</a></td><td><a href="#api-krak-fun-takewhile">takeWhile</a></td><td><a href="#api-krak-fun-toarray">toArray</a></td><td><a href="#api-krak-fun-toarraywithkeys">toArrayWithKeys</a></td><td><a href="#api-krak-fun-topairs">toPairs</a></td><td><a href="#api-krak-fun-updateindexin">updateIndexIn</a></td><td><a href="#api-krak-fun-values">values</a></td></tr><tr><td><a href="#api-krak-fun-when">when</a></td><td><a href="#api-krak-fun-withstate">withState</a></td><td><a href="#api-krak-fun-within">within</a></td><td><a href="#api-krak-fun-without">without</a></td><td><a href="#api-krak-fun-zip">zip</a></td></tr></table>

<h3 id="api-krak-fun-all">all(callable $predicate, iterable $iter): bool</h3>

**Name:** `Krak\Fun\all`

Returns true if the predicate returns true on all of the items:

```php
$res = all(function ($v) {
    return $v % 2 == 0;
}, [2, 4, 6]);
expect($res)->equal(true);
```

Returns false if the predicate returns false on any of the items:

```php
$res = all(function ($v) {
    return $v % 2 == 0;
}, [1, 2, 4, 6]);
expect($res)->equal(false);
```



<h3 id="api-krak-fun-any">any(callable $predicate, iterable $iter): bool</h3>

**Name:** `Krak\Fun\any`

Returns true if the predicate returns true on any of the items:

```php
$res = any(function ($v) {
    return $v % 2 == 0;
}, [1, 3, 4, 5]);
expect($res)->equal(true);
```

Returns false if the predicate returns false on all of the items:

```php
$res = any(function ($v) {
    return $v % 2 == 0;
}, [1, 3, 5]);
expect($res)->equal(false);
```



<h3 id="api-krak-fun-arraycompact">arrayCompact(iterable $iter): array</h3>

**Name:** `Krak\Fun\arrayCompact`

It will remove all nulls from an iterable and return an array:

```php
$res = arrayCompact([1, 2, null, null, 3]);
expect(\array_values($res))->equal([1, 2, 3]);
```

Keep in mind that the keys will be preserved when using arrayCompact, so make sure to use array_values if you want to ignore keys.

<h3 id="api-krak-fun-arrayfilter">arrayFilter(callable $fn, iterable $data): array</h3>

**Name:** `Krak\Fun\arrayFilter`

Alias of array_filter:

```php
$res = arrayFilter(partial(op, '<', 2), [1, 2, 3]);
expect($res)->equal([1]);
```

Filters iterables as well as arrays:

```php
$res = arrayFilter(partial(op, '<', 2), range(1, 3));
expect($res)->equal([1]);
```



<h3 id="api-krak-fun-arraymap">arrayMap(callable $fn, iterable $data): array</h3>

**Name:** `Krak\Fun\arrayMap`

Alias of array_map:

```php
$res = arrayMap(partial(op, '*', 2), [1, 2, 3]);
expect($res)->equal([2, 4, 6]);
```

Maps iterables as well as arrays:

```php
$res = arrayMap(partial(op, '*', 2), range(1, 3));
expect($res)->equal([2, 4, 6]);
```



<h3 id="api-krak-fun-arrayreindex">arrayReindex(callable $fn, iterable $iter): array</h3>

**Name:** `Krak\Fun\arrayReindex`

Re-indexes a collection via a callable into an associative array:

```php
$res = arrayReindex(function ($v) {
    return $v['id'];
}, [['id' => 2], ['id' => 3], ['id' => 1]]);
expect($res)->equal([2 => ['id' => 2], 3 => ['id' => 3], 1 => ['id' => 1]]);
```



<h3 id="api-krak-fun-assign">assign($obj, iterable $iter)</h3>

**Name:** `Krak\Fun\assign`

Assigns iterable keys and values to an object:

```php
$obj = new \StdClass();
$obj = assign($obj, ['a' => 1, 'b' => 2]);
expect($obj->a)->equal(1);
expect($obj->b)->equal(2);
```



<h3 id="api-krak-fun-chain">chain(iterable ...$iters)</h3>

**Name:** `Krak\Fun\chain`

Chains iterables together into one iterable:

```php
$res = chain([1], range(2, 3));
expect(toArray($res))->equal([1, 2, 3]);
```



<h3 id="api-krak-fun-chunk">chunk(int $size, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\chunk`

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



<h3 id="api-krak-fun-chunkby">chunkBy(callable $fn, iterable $iter, ?int $maxSize = null): iterable</h3>

**Name:** `Krak\Fun\chunkBy`

Chunks items together off of the result from the callable:

```php
$items = ['aa', 'ab', 'ac', 'ba', 'bb', 'bc', 'ca', 'cb', 'cc'];
$chunks = chunkBy(function (string $item) {
    return $item[0];
    // return first char
}, $items);
expect(toArray($chunks))->equal([['aa', 'ab', 'ac'], ['ba', 'bb', 'bc'], ['ca', 'cb', 'cc']]);
```

Allows a maxSize to prevent chunks from exceeding a limit:

```php
$items = ['aa', 'ab', 'ac', 'ba', 'bb', 'bc', 'ca', 'cb', 'cc'];
$chunks = chunkBy(function (string $item) {
    return $item[0];
    // return first char
}, $items, 2);
expect(toArray($chunks))->equal([['aa', 'ab'], ['ac'], ['ba', 'bb'], ['bc'], ['ca', 'cb'], ['cc']]);
```



<h3 id="api-krak-fun-compact">compact(iterable $iter): iterable</h3>

**Name:** `Krak\Fun\compact`

Removes all null values from an iterable:

```php
$res = compact([1, null, 2, 3, null, null, 4]);
expect(toArray($res))->equal([1, 2, 3, 4]);
```



<h3 id="api-krak-fun-compose">compose(callable ...$fns)</h3>

**Name:** `Krak\Fun\compose`

Composes functions together. compose(f, g)(x) == f(g(x)):

```php
$mul2 = Curried\op('*')(2);
$add3 = Curried\op('+')(3);
$add3ThenMul2 = compose($mul2, $add3);
$res = $add3ThenMul2(5);
expect($res)->equal(16);
```

Allows an empty initial argument:

```php
$res = compose(Curried\reduce(function ($acc, $v) {
    return $acc + $v;
}, 0), function () {
    yield from [1, 2, 3];
})();
expect($res)->equal(6);
```



<h3 id="api-krak-fun-construct">construct($className, ...$args)</h3>

**Name:** `Krak\Fun\construct`

Constructs (instantiates) a new class with the given arguments:

```php
$res = construct(\ArrayObject::class, [1, 2, 3]);
expect($res->count())->equal(3);
```



<h3 id="api-krak-fun-curry">curry(callable $fn, int $num = 1)</h3>

**Name:** `Krak\Fun\curry`

currys the given function $n times:

```php
$res = curry(_idArgs::class, 2)(1)(2)(3);
expect($res)->equal([1, 2, 3]);
```

Given a function definition: (a, b) -> c. A curried version will look like (a) -> (b) -> c

<h3 id="api-krak-fun-differencewith">differenceWith(callable $cmp, iterable $a, iterable $b)</h3>

**Name:** `Krak\Fun\differenceWith`

Takes the difference between two iterables with a given comparator:

```php
$res = differenceWith(partial(op, '==='), [1, 2, 3, 4, 5], [2, 3, 4]);
expect(toArray($res))->equal([1, 5]);
```



<h3 id="api-krak-fun-dd">dd($value, callable $dump = null, callable $die = null)</h3>

**Name:** `Krak\Fun\dd`

dumps and dies:

```php
$res = null;
$died = false;
$dump = function ($v) use(&$res) {
    $res = $v;
};
$die = function () use(&$died) {
    $died = true;
};
dd(1, $dump, $die);
expect($res)->equal(1);
expect($died)->equal(true);
```



<h3 id="api-krak-fun-drop">drop(int $num, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\drop`

Drops the first num items from an iterable:

```php
$res = drop(2, range(0, 3));
expect(toArray($res))->equal([2, 3]);
```



<h3 id="api-krak-fun-dropwhile">dropWhile(callable $predicate, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\dropWhile`

Drops elements from the iterable while the predicate returns true:

```php
$res = dropWhile(Curried\op('>')(0), [2, 1, 0, 1, 2]);
expect(toArray($res))->equal([0, 1, 2]);
```



<h3 id="api-krak-fun-each">each(callable $handle, iterable $iter)</h3>

**Name:** `Krak\Fun\each`

Invokes a callable on each item in an iterable:

```php
$state = [(object) ['id' => 1], (object) ['id' => 2]];
each(function ($item) {
    $item->id += 1;
}, $state);
expect([$state[0]->id, $state[1]->id])->equal([2, 3]);
```

Normally using php foreach should suffice for iterating over an iterable; however, php variables in foreach loops are not scoped whereas closures are.

<h3 id="api-krak-fun-filter">filter(callable $predicate, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\filter`

Lazily filters an iterable off of a predicate that should return true or false. If true, keep the data, else remove the data from the iterable:

```php
$values = filter(partial(op, '>', 2), [1, 2, 3, 4]);
// keep all items that are greater than 2
expect(toArray($values))->equal([3, 4]);
```



<h3 id="api-krak-fun-filterkeys">filterKeys(callable $predicate, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\filterKeys`

Filters an iterable off of the keys:

```php
$res = filterKeys(Curried\inArray(['a', 'b']), ['a' => 1, 'b' => 2, 'c' => 3]);
expect(toArrayWithKeys($res))->equal(['a' => 1, 'b' => 2]);
```



<h3 id="api-krak-fun-flatmap">flatMap(callable $map, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\flatMap`

Maps and then flattens an iterable:

```php
$res = flatMap(function ($v) {
    return [-$v, $v];
}, range(1, 3));
expect(toArray($res))->equal([-1, 1, -2, 2, -3, 3]);
```

flatMap is perfect for when you want to map an iterable and also add elements to the resulting iterable.

<h3 id="api-krak-fun-flatten">flatten(iterable $iter, $levels = INF): iterable</h3>

**Name:** `Krak\Fun\flatten`

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

Flattening zero levels does nothing:

```php
$res = flatten([1, [2]], 0);
expect(toArray($res))->equal([1, [2]]);
```



<h3 id="api-krak-fun-flip">flip(iterable $iter): iterable</h3>

**Name:** `Krak\Fun\flip`

Flips the keys => values of an iterable to values => keys:

```php
$res = flip(['a' => 0, 'b' => 1]);
expect(toArray($res))->equal(['a', 'b']);
```



<h3 id="api-krak-fun-frompairs">fromPairs(iterable $iter): iterable</h3>

**Name:** `Krak\Fun\fromPairs`

Converts an iterable of tuples [$key, $value] into an associative iterable:

```php
$res = fromPairs([['a', 1], ['b', 2]]);
expect(toArrayWithKeys($res))->equal(['a' => 1, 'b' => 2]);
```



<h3 id="api-krak-fun-groupby">groupBy(callable $fn, iterable $iter, ?int $maxSize = null): iterable</h3>

**Name:** `Krak\Fun\groupBy`

Alias of chunkBy

Groups items together off of the result from the callable:

```php
$items = ['aa', 'ab', 'ac', 'ba', 'bb', 'bc', 'ca', 'cb', 'cc'];
$groupedItems = groupBy(function (string $item) {
    return $item[0];
    // return first char
}, $items);
expect(toArray($groupedItems))->equal([['aa', 'ab', 'ac'], ['ba', 'bb', 'bc'], ['ca', 'cb', 'cc']]);
```

Allows a maxSize to prevent groups from exceeding a limit:

```php
$items = ['aa', 'ab', 'ac', 'ba', 'bb', 'bc', 'ca', 'cb', 'cc'];
$groupedItems = groupBy(function (string $item) {
    return $item[0];
    // return first char
}, $items, 2);
expect(toArray($groupedItems))->equal([['aa', 'ab'], ['ac'], ['ba', 'bb'], ['bc'], ['ca', 'cb'], ['cc']]);
```



<h3 id="api-krak-fun-hasindexin">hasIndexIn(array $keys, array $data): bool</h3>

**Name:** `Krak\Fun\hasIndexIn`

Checks if a nested index exists in the given data:

```php
$res = hasIndexIn(['a', 'b', 'c'], ['a' => ['b' => ['c' => null]]]);
expect($res)->equal(true);
```

Returns false if any of the indexes do not exist in the data:

```php
$res = hasIndexIn(['a', 'b', 'c'], ['a' => ['b' => []]]);
expect($res)->equal(false);
```



<h3 id="api-krak-fun-head">head(iterable $iter)</h3>

**Name:** `Krak\Fun\head`

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



<h3 id="api-krak-fun-inarray">inArray(array $set, $item): bool</h3>

**Name:** `Krak\Fun\inArray`

Checks if an item is within an array of items:

```php
$res = inArray([1, 2, 3], 2);
expect($res)->equal(true);
```



<h3 id="api-krak-fun-index">index($key, $data, $else = null)</h3>

**Name:** `Krak\Fun\index`

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

Also works with objects that implement ArrayAccess:

```php
class MyClass implements \ArrayAccess
{
    private $container = [];
    public function __construct()
    {
        $this->container = ['one' => 1, 'two' => 2];
    }
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
    public function offsetSet($offset, $value)
    {
        /* ... */
    }
    public function offsetUnset($offset)
    {
        /* ... */
    }
}
$object = new MyClass();
expect(index('two', $object))->equal(2);
expect(index('three', $object, 'else'))->equal('else');
```



<h3 id="api-krak-fun-indexin">indexIn(array $keys, array $data, $else = null)</h3>

**Name:** `Krak\Fun\indexIn`

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



<h3 id="api-krak-fun-indexof">indexOf(callable $predicate, iterable $iter)</h3>

**Name:** `Krak\Fun\indexOf`

Searches for an element and returns the key if found:

```php
$res = indexOf(partial(op, '==', 'b'), ['a', 'b', 'c']);
expect($res)->equal(1);
```



<h3 id="api-krak-fun-isnull">isNull($val)</h3>

**Name:** `Krak\Fun\isNull`

alias for is_null:

```php
expect(isNull(null))->equal(true);
expect(isNull(0))->equal(false);
```



<h3 id="api-krak-fun-iter">iter($iter): \Iterator</h3>

**Name:** `Krak\Fun\iter`

Converts any iterable into a proper instance of Iterator.

Can convert arrays:

```php
expect(iter([1, 2, 3]))->instanceof('Iterator');
```

Can convert an Iterator:

```php
expect(iter(new \ArrayIterator([1, 2, 3])))->instanceof('Iterator');
```

Can convert objects:

```php
$obj = (object) ['a' => 1, 'b' => 2];
expect(iter($obj))->instanceof('Iterator');
expect(toArrayWithKeys(iter($obj)))->equal(['a' => 1, 'b' => 2]);
```

Can convert any iterable:

```php
$a = new class implements \IteratorAggregate
{
    public function getIterator()
    {
        return new \ArrayIterator([1, 2, 3]);
    }
};
expect(iter($a))->instanceof('Iterator');
expect(toArray(iter($a)))->equal([1, 2, 3]);
```

Can convert strings:

```php
expect(iter('abc'))->instanceof('Iterator');
expect(toArray(iter('abc')))->equal(['a', 'b', 'c']);
```

Will throw an exception otherwise:

```php
expect(function () {
    iter(1);
})->throw('LogicException', 'Iter could not be converted into an iterable.');
```



<h3 id="api-krak-fun-join">join(string $sep, iterable $iter)</h3>

**Name:** `Krak\Fun\join`

Joins an iterable with a given separator:

```php
$res = join(",", range(1, 3));
expect($res)->equal("1,2,3");
```



<h3 id="api-krak-fun-keys">keys(iterable $iter): iterable</h3>

**Name:** `Krak\Fun\keys`

Yields only the keys of an in iterable:

```php
$keys = keys(['a' => 1, 'b' => 2]);
expect(toArray($keys))->equal(['a', 'b']);
```



<h3 id="api-krak-fun-map">map(callable $predicate, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\map`

Lazily maps an iterable's values to a different set:

```php
$values = map(partial(op, '*', 2), [1, 2, 3, 4]);
expect(toArray($values))->equal([2, 4, 6, 8]);
```



<h3 id="api-krak-fun-mapaccum">mapAccum(callable $fn, iterable $iter, $acc = null)</h3>

**Name:** `Krak\Fun\mapAccum`

Maps a function to each element of a list while passing in an accumulator to accumulate over every iteration:

```php
$data = iter('abcd');
[$totalSort, $values] = mapAccum(function ($acc, $value) {
    return [$acc + 1, ['name' => $value, 'sort' => $acc]];
}, iter('abcd'), 0);
expect($totalSort)->equal(4);
expect($values)->equal([['name' => 'a', 'sort' => 0], ['name' => 'b', 'sort' => 1], ['name' => 'c', 'sort' => 2], ['name' => 'd', 'sort' => 3]]);
```

Note: mapAccum converts the interable into an array and is not lazy like most of the other functions in this library

<h3 id="api-krak-fun-mapkeys">mapKeys(callable $predicate, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\mapKeys`

Lazily maps an iterable's keys to a different set:

```php
$keys = mapKeys(partial(op, '.', '_'), ['a' => 1, 'b' => 2]);
expect(toArrayWithKeys($keys))->equal(['a_' => 1, 'b_' => 2]);
```



<h3 id="api-krak-fun-mapkeyvalue">mapKeyValue(callable $fn, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\mapKeyValue`

Lazily maps an iterable's key/value tuples to a different set:

```php
$keys = mapKeyValue(function ($kv) {
    [$key, $value] = $kv;
    return ["{$key}_", $value * $value];
}, ['a' => 1, 'b' => 2]);
expect(toArrayWithKeys($keys))->equal(['a_' => 1, 'b_' => 4]);
```



<h3 id="api-krak-fun-mapon">mapOn(array $maps, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\mapOn`

Maps values on specific keys:

```php
$values = mapOn(['a' => partial(op, '*', 3), 'b' => partial(op, '+', 1)], ['a' => 1, 'b' => 2, 'c' => 3]);
expect(toArray($values))->equal([3, 3, 3]);
```



<h3 id="api-krak-fun-nullable">nullable(callable $fn, $value)</h3>

**Name:** `Krak\Fun\nullable`

Performs the callable if the value is not null:

```php
expect(nullable('intval', '0'))->equal(0);
```

Returns null if the value is null:

```php
expect(nullable('intval', null))->equal(null);
```



<h3 id="api-krak-fun-oneach">onEach(callable $handle, iterable $iter)</h3>

**Name:** `Krak\Fun\onEach`

Duplicate of each.

Invokes a callable on each item in an iterable:

```php
$state = [(object) ['id' => 1], (object) ['id' => 2]];
onEach(function ($item) {
    $item->id += 1;
}, $state);
expect([$state[0]->id, $state[1]->id])->equal([2, 3]);
```

Normally using php foreach should suffice for iterating over an iterable; however, php variables in foreach loops are not scoped whereas closures are.

<h3 id="api-krak-fun-op">op(string $op, $b, $a)</h3>

**Name:** `Krak\Fun\op`

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

Supports other operators:

```php
$ops = [['+', [2, 3], 5], ['-', [2, 3], 1], ['*', [2, 3], 6], ['**', [2, 3], 9], ['/', [2, 3], 1.5], ['%', [2, 3], 1], ['.', ['b', 'a'], 'ab']];
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



<h3 id="api-krak-fun-pad">pad(int $size, iterable $iter, $padValue = null): iterable</h3>

**Name:** `Krak\Fun\pad`

Pads an iterable to a specific size:

```php
$res = pad(5, [1, 2, 3]);
expect(toArray($res))->equal([1, 2, 3, null, null]);
```

Allows custom pad values:

```php
$res = pad(5, [1, 2, 3], 0);
expect(toArray($res))->equal([1, 2, 3, 0, 0]);
```

Pads nothing if iterable is the same size as pad size:

```php
$res = pad(5, [1, 2, 3, 4, 5]);
expect(toArray($res))->equal([1, 2, 3, 4, 5]);
```

Pads nothing if iterable is greater than pad size:

```php
$res = pad(5, [1, 2, 3, 4, 5, 6]);
expect(toArray($res))->equal([1, 2, 3, 4, 5, 6]);
```

Ignores keys of original iterable:

```php
$res = pad(3, ['a' => 1, 'b' => 2]);
expect(toArrayWithKeys($res))->equal([1, 2, null]);
```



<h3 id="api-krak-fun-partial">partial(callable $fn, ...$appliedArgs)</h3>

**Name:** `Krak\Fun\partial`

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



<h3 id="api-krak-fun-partition">partition(callable $partition, iterable $iter, int $numParts = 2): array</h3>

**Name:** `Krak\Fun\partition`

Splits an iterable into different arrays based off of a predicate. The predicate should return the index to partition the data into:

```php
list($left, $right) = partition(function ($v) {
    return $v < 3 ? 0 : 1;
}, [1, 2, 3, 4]);
expect([$left, $right])->equal([[1, 2], [3, 4]]);
```



<h3 id="api-krak-fun-pick">pick(iterable $fields, array $data): array</h3>

**Name:** `Krak\Fun\pick`

Picks only the given fields from a structured array:

```php
$res = pick(['a', 'b'], ['a' => 1, 'b' => 2, 'c' => 3]);
expect($res)->equal(['a' => 1, 'b' => 2]);
```

Can be used in curried form:

```php
$res = arrayMap(Curried\pick(['id', 'name']), [['id' => 1, 'name' => 'Foo', 'slug' => 'foo'], ['id' => 2, 'name' => 'Bar', 'slug' => 'bar']]);
expect($res)->equal([['id' => 1, 'name' => 'Foo'], ['id' => 2, 'name' => 'Bar']]);
```



<h3 id="api-krak-fun-pickby">pickBy(callable $pick, array $data): array</h3>

**Name:** `Krak\Fun\pickBy`

Picks only the fields that match the pick function from a structured array:

```php
$res = pickBy(Curried\spread(function (string $key, int $value) : bool {
    return $value % 2 === 0;
}), ['a' => 1, 'b' => 2, 'c' => 3]);
expect($res)->equal(['b' => 2]);
```



<h3 id="api-krak-fun-pipe">pipe(callable ...$fns)</h3>

**Name:** `Krak\Fun\pipe`

Creates a function that pipes values from one func to the next.:

```php
$add3 = Curried\op('+')(3);
$mul2 = Curried\op('*')(2);
$add3ThenMul2 = pipe($add3, $mul2);
$res = $add3ThenMul2(5);
expect($res)->equal(16);
```

Allows an empty initial argument:

```php
$res = pipe(function () {
    yield from [1, 2, 3];
}, Curried\reduce(function ($acc, $v) {
    return $acc + $v;
}, 0))();
expect($res)->equal(6);
```

`pipe` and `compose` are sister functions and do the same thing except the functions are composed in reverse order. pipe(f, g)(x) = g(f(x))

<h3 id="api-krak-fun-product">product(iterable ...$iters): iterable</h3>

**Name:** `Krak\Fun\product`

Creates a cartesian product of multiple sets:

```php
$res = product([1, 2], [3, 4], [5, 6]);
expect(toArray($res))->equal([[1, 3, 5], [1, 3, 6], [1, 4, 5], [1, 4, 6], [2, 3, 5], [2, 3, 6], [2, 4, 5], [2, 4, 6]]);
```



<h3 id="api-krak-fun-prop">prop(string $key, $data, $else = null)</h3>

**Name:** `Krak\Fun\prop`

Accesses a property from an object:

```php
$obj = new \StdClass();
$obj->id = 1;
$res = prop('id', $obj);
expect($res)->equal(1);
```

If no property exists, it will return the $else value:

```php
$obj = new \StdClass();
$res = prop('id', $obj, 2);
expect($res)->equal(2);
```



<h3 id="api-krak-fun-propin">propIn(array $props, $obj, $else = null)</h3>

**Name:** `Krak\Fun\propIn`

Accesses a property deep in an object tree:

```php
$obj = new \StdClass();
$obj->id = 1;
$obj->child = new \StdClass();
$obj->child->id = 2;
$res = propIn(['child', 'id'], $obj);
expect($res)->equal(2);
```

If any property is missing in the tree, it will return the $else value:

```php
$obj = new \StdClass();
$obj->id = 1;
$obj->child = new \StdClass();
$res = propIn(['child', 'id'], $obj, 3);
expect($res)->equal(3);
```



<h3 id="api-krak-fun-range">range($start, $end, $step = null)</h3>

**Name:** `Krak\Fun\range`

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



<h3 id="api-krak-fun-reduce">reduce(callable $reduce, iterable $iter, $acc = null)</h3>

**Name:** `Krak\Fun\reduce`

Reduces an iterable into a single value:

```php
$res = reduce(function ($acc, $v) {
    return $acc + $v;
}, range(1, 3), 0);
expect($res)->equal(6);
```



<h3 id="api-krak-fun-reducekeyvalue">reduceKeyValue(callable $reduce, iterable $iter, $acc = null)</h3>

**Name:** `Krak\Fun\reduceKeyValue`

Reduces an iterables key value pairs into a value:

```php
$res = reduceKeyValue(function ($acc, $kv) {
    [$key, $value] = $kv;
    return $acc . $key . $value;
}, fromPairs([['a', 1], ['b', 2]]), "");
expect($res)->equal("a1b2");
```



<h3 id="api-krak-fun-reindex">reindex(callable $fn, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\reindex`

Re-indexes a collection via a callable:

```php
$res = reindex(function ($v) {
    return $v['id'];
}, [['id' => 2], ['id' => 3], ['id' => 1]]);
expect(toArrayWithKeys($res))->equal([2 => ['id' => 2], 3 => ['id' => 3], 1 => ['id' => 1]]);
```



<h3 id="api-krak-fun-retry">retry(callable $fn, $shouldRetry = null)</h3>

**Name:** `Krak\Fun\retry`

Executes a function and retries if an exception is thrown:

```php
$i = 0;
$res = retry(function () use(&$i) {
    $i += 1;
    if ($i <= 1) {
        throw new \Exception('bad');
    }
    return $i;
});
expect($res)->equal(2);
```

Only retries $maxTries times else it gives up and bubbles the exception:

```php
expect(function () {
    $i = 0;
    retry(function () use(&$i) {
        $i += 1;
        throw new \Exception((string) $i);
    }, 5);
})->throw('Exception', '6');
```

Retries until $shouldRetry returns false:

```php
$i = 0;
expect(function () {
    $res = retry(function () use(&$i) {
        $i += 1;
        throw new \Exception((string) $i);
    }, function ($numRetries, \Throwable $t = null) {
        return $numRetries < 2;
    });
})->throw('Exception', '2');
```

Sends numRetries into the main fn:

```php
$res = retry(function ($numRetries) {
    if (!$numRetries) {
        throw new Exception('bad');
    }
    return $numRetries;
}, 2);
expect($res)->equal(1);
```

Keep in mind that maxTries determines the number of *re*-tries. This means the function will execute maxTries + 1 times since the first invocation is not a retry.

<h3 id="api-krak-fun-search">search(callable $predicate, iterable $iter)</h3>

**Name:** `Krak\Fun\search`

Searches for an element in a collection where the callable returns true:

```php
$res = search(function ($v) {
    return $v['id'] == 2;
}, [['id' => 1], ['id' => 2], ['id' => 3]]);
expect($res)->equal(['id' => 2]);
```

Returns null if no element was found:

```php
$res = search(function ($v) {
    return false;
}, [['id' => 1], ['id' => 2], ['id' => 3]]);
expect($res)->equal(null);
```



<h3 id="api-krak-fun-setindex">setIndex($key, $value, array $data)</h3>

**Name:** `Krak\Fun\setIndex`

Sets an index in an array:

```php
$res = setIndex('a', 1, []);
expect($res['a'])->equal(1);
```



<h3 id="api-krak-fun-setindexin">setIndexIn(array $keys, $value, array $data)</h3>

**Name:** `Krak\Fun\setIndexIn`

Sets a nested index in an array:

```php
$res = setIndexIn(['a', 'b'], 1, ['a' => []]);
expect($res['a']['b'])->equal(1);
```



<h3 id="api-krak-fun-setprop">setProp(string $key, $value, $data)</h3>

**Name:** `Krak\Fun\setProp`

Sets a property in an object:

```php
$res = setProp('a', 1, (object) []);
expect($res->a)->equal(1);
```



<h3 id="api-krak-fun-slice">slice(int $start, iterable $iter, $length = INF): iterable</h3>

**Name:** `Krak\Fun\slice`

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

will not consume the iterator once the slice has been yielded:

```php
$i = 0;
$gen = function () use(&$i) {
    foreach (range(0, 4) as $v) {
        $i = $v;
        (yield $i);
    }
};
$sliced = toArray(slice(1, $gen(), 2));
expect($sliced)->equal([1, 2]);
expect($i)->equal(2);
```



<h3 id="api-krak-fun-sortfromarray">sortFromArray(callable $fn, array $orderedElements, iterable $iter): array</h3>

**Name:** `Krak\Fun\sortFromArray`

Sort an iterable with a given array of ordered elements to sort by:

```php
$data = [['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'B'], ['id' => 3, 'name' => 'C']];
$res = sortFromArray(Curried\index('id'), [2, 3, 1], $data);
expect(arrayMap(Curried\index('name'), $res))->equal(['B', 'C', 'A']);
```

Throws an exception if any item in the iterable is not within the orderedElements:

```php
expect(function () {
    $data = [['id' => 1]];
    $res = sortFromArray(Curried\index('id'), [], $data);
})->throw(\LogicException::class, 'Cannot sort element key 1 because it does not exist in the ordered elements.');
```

I've found this to be very useful when you fetch records from a database with a WHERE IN clause, and you need to make sure the results are in the same order as the ids in the WHERE IN clause.

<h3 id="api-krak-fun-spread">spread(callable $fn, array $data)</h3>

**Name:** `Krak\Fun\spread`

Spreads an array of arguments to a callable:

```php
$res = spread(function ($a, $b) {
    return $a . $b;
}, ['a', 'b']);
expect($res)->equal('ab');
```

Can be used in the curried form to unpack tuple arguments:

```php
$res = arrayMap(Curried\spread(function (string $first, int $second) {
    return $first . $second;
}), [['a', 1], ['b', 2]]);
expect($res)->equal(['a1', 'b2']);
```

Note: this is basically just an alias for `call_user_func_array` or simply a functional wrapper around the `...` (spread) operator.

<h3 id="api-krak-fun-take">take(int $num, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\take`

Takes the first num items from an iterable:

```php
$res = take(2, range(0, 10));
expect(toArray($res))->equal([0, 1]);
```



<h3 id="api-krak-fun-takewhile">takeWhile(callable $predicate, iterable $iter): iterable</h3>

**Name:** `Krak\Fun\takeWhile`

Takes elements from an iterable while the $predicate returns true:

```php
$res = takeWhile(Curried\op('>')(0), [2, 1, 0, 1, 2]);
expect(toArray($res))->equal([2, 1]);
```



<h3 id="api-krak-fun-toarray">toArray(iterable $iter): array</h3>

**Name:** `Krak\Fun\toArray`

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



<h3 id="api-krak-fun-toarraywithkeys">toArrayWithKeys(iterable $iter): array</h3>

**Name:** `Krak\Fun\toArrayWithKeys`

can convert to an array and keep the keys:

```php
$gen = function () {
    (yield 'a' => 1);
    (yield 'b' => 2);
};
expect(toArrayWithKeys($gen()))->equal(['a' => 1, 'b' => 2]);
```



<h3 id="api-krak-fun-topairs">toPairs(iterable $iter): iterable</h3>

**Name:** `Krak\Fun\toPairs`

Transforms an associative array into an iterable of tuples [$key, $value]:

```php
$res = toPairs(['a' => 1, 'b' => 2]);
expect(toArray($res))->equal([['a', 1], ['b', 2]]);
```



<h3 id="api-krak-fun-updateindexin">updateIndexIn(array $keys, callable $update, array $data): array</h3>

**Name:** `Krak\Fun\updateIndexIn`

Updates a nested element within a deep array structure:

```php
$data = ['a' => ['b' => ['c' => 3]]];
$data = updateIndexIn(['a', 'b', 'c'], function ($v) {
    return $v * $v;
}, $data);
expect($data)->equal(['a' => ['b' => ['c' => 9]]]);
```

Throws an exception if nested key does not exist:

```php
expect(function () {
    $data = ['a' => ['b' => ['c' => 9]]];
    updateIndexIn(['a', 'c', 'c'], function () {
    }, $data);
})->throw(\RuntimeException::class, 'Could not updateIn because the keys a -> c -> c could not be found.');
```



<h3 id="api-krak-fun-values">values(iterable $iter): iterable</h3>

**Name:** `Krak\Fun\values`

Exports only the values of an iterable:

```php
$res = values(['a' => 1, 'b' => 2]);
expect(toArrayWithKeys($res))->equal([1, 2]);
```



<h3 id="api-krak-fun-when">when(callable $if, callable $then, $value)</h3>

**Name:** `Krak\Fun\when`

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



<h3 id="api-krak-fun-withstate">withState(callable $fn, $initialState = null)</h3>

**Name:** `Krak\Fun\withState`

Decorate a function with accumulating state:

```php
$fn = withState(function ($state, $v) {
    return [$state + 1, $state . ': ' . $v];
}, 1);
$res = arrayMap($fn, iter('abcd'));
expect($res)->equal(['1: a', '2: b', '3: c', '4: d']);
```



<h3 id="api-krak-fun-within">within(array $fields, iterable $iter): \Iterator</h3>

**Name:** `Krak\Fun\within`

Only allows keys within the given array to stay:

```php
$data = flip(iter('abcd'));
$res = within(['a', 'c'], $data);
expect(toArrayWithKeys($res))->equal(['a' => 0, 'c' => 2]);
```



<h3 id="api-krak-fun-without">without(array $fields, iterable $iter): \Iterator</h3>

**Name:** `Krak\Fun\without`

Filters an iterable to be without the given keys:

```php
$data = flip(iter('abcd'));
$res = without(['a', 'c'], $data);
expect(toArrayWithKeys($res))->equal(['b' => 1, 'd' => 3]);
```



<h3 id="api-krak-fun-zip">zip(iterable ...$iters): \Iterator</h3>

**Name:** `Krak\Fun\zip`

Zips multiple iterables into an iterable n-tuples:

```php
$res = zip(iter('abc'), range(1, 3), [4, 5, 6]);
expect(toArray($res))->equal([['a', 1, 4], ['b', 2, 5], ['c', 3, 6]]);
```

Returns an empty iterable if no iters are present:

```php
expect(toArray(zip()))->equal([]);
```


