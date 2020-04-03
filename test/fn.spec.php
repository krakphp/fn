<?php

namespace Krak\Fun;

use const Krak\Fun\{toArray, id, op};
use stdClass;

function _idArgs(...$args) {
    return $args;
}

describe('Fun', function() {
    it('can perform functional operations', function() {
        $res = compose(toArray, Curried\map(partial(op, '*', 3)), Curried\filter(partial(op, '>', 2)))([1,2,3,4]);
        expect($res)->equal([9, 12]);
    });

    describe('autoCurry', function() {
        it('can call a function if all args are available', function() {
            $res = autoCurry([1,2,3], 3, _idArgs::class);
            expect($res)->equal([1,2,3]);
        });
        it('can partially apply a function if all but one arg is available', function() {
            $res = autoCurry([1,2], 3, _idArgs::class)(3);
            expect($res)->equal([1,2,3]);
        });
        it('can curry a partially applied function if less than n - 1 args are available', function() {
            $res = autoCurry([1], 3, _idArgs::class)(2)(3);
            expect($res)->equal([1,2,3]);
        });
        it('can curry a function if no args are available', function() {
            $res = autoCurry([], 3, _idArgs::class)(1)(2)(3);
            expect($res)->equal([1,2,3]);
        });
    });
    describe('all', function() {
        docFn(all::class);
        test('Returns true if the predicate returns true on all of the items', function() {
            $res = all(function($v) { return $v % 2 == 0; }, [2,4,6]);
            expect($res)->equal(true);
        });
        test('Returns false if the predicate returns false on any of the items', function() {
            $res = all(function($v) { return $v % 2 == 0; }, [1,2,4,6]);
            expect($res)->equal(false);
        });
    });
    describe('any', function() {
        docFn(any::class);
        test('Returns true if the predicate returns true on any of the items', function() {
            $res = any(function($v) { return $v % 2 == 0; }, [1,3,4,5]);
            expect($res)->equal(true);
        });
        test('Returns false if the predicate returns false on all of the items', function() {
            $res = any(function($v) { return $v % 2 == 0; }, [1,3,5]);
            expect($res)->equal(false);
        });
    });
    describe('arrayCompact', function() {
        docFn(arrayCompact::class);
        test('It will remove all nulls from an iterable and return an array', function() {
            $res = arrayCompact([1,2,null,null,3]);
            expect(\array_values($res))->equal([1,2,3]);
        });
        docOutro('Keep in mind that the keys will be preserved when using arrayCompact, so make sure to use array_values if you want to ignore keys.');
    });
    describe('arrayFilter', function() {
        docFn(arrayFilter::class);
        test('Alias of array_filter', function() {
            $res = arrayFilter(partial(op, '<', 2), [1,2,3]);
            expect($res)->equal([1]);
        });
        test('Filters iterables as well as arrays', function() {
            $res = arrayFilter(partial(op, '<', 2), range(1, 3));
            expect($res)->equal([1]);
        });
    });
    describe('arrayMap', function() {
        docFn(arrayMap::class);
        test('Alias of array_map', function() {
            $res = arrayMap(partial(op, '*', 2), [1,2,3]);
            expect($res)->equal([2,4,6]);
        });
        test('Maps iterables as well as arrays', function() {
            $res = arrayMap(partial(op, '*', 2), range(1, 3));
            expect($res)->equal([2,4,6]);
        });
    });
    describe('arrayReindex', function() {
        docFn(arrayReindex::class);
        test('Re-indexes a collection via a callable into an associative array', function() {
            $res = arrayReindex(function($v) {
                return $v['id'];
            }, [['id' => 2], ['id' => 3], ['id' => 1]]);

            expect($res)->equal([
                2 => ['id' => 2],
                3 => ['id' => 3],
                1 => ['id' => 1],
            ]);
        });
    });
    describe('assign', function() {
        docFn(assign::class);
        test('Assigns iterable keys and values to an object', function() {
            $obj = new \StdClass();
            $obj = assign($obj, ['a' => 1, 'b' => 2]);
            expect($obj->a)->equal(1);
            expect($obj->b)->equal(2);
        });
    });

    describe('chain', function() {
        docFn(chain::class);
        test('Chains iterables together into one iterable', function() {
            $res = chain([1], range(2, 3));
            expect(toArray($res))->equal([1,2,3]);
        });
    });
    describe('chunk', function() {
        docFn(chunk::class);
        test('Chunks an iterable into equal sized chunks.', function() {
            $res = chunk(2, [1,2,3,4]);
            expect(toArray($res))->equal([[1,2], [3,4]]);
        });
        test('If there is any remainder, it is yielded as is', function() {
            $res = chunk(3, [1,2,3,4]);
            expect(toArray($res))->equal([[1,2,3], [4]]);
        });
    });
    describe('chunkBy', function() {
        docFn(chunkBy::class);
        test('Chunks items together off of the result from the callable', function() {
            $items = ['aa', 'ab', 'ac', 'ba', 'bb', 'bc', 'ca', 'cb', 'cc'];
            $chunks = chunkBy(function(string $item) {
                return $item[0]; // return first char
            }, $items);
            expect(toArray($chunks))->equal([
                ['aa', 'ab', 'ac'],
                ['ba', 'bb', 'bc'],
                ['ca', 'cb', 'cc']
            ]);
        });
        test('Allows a maxSize to prevent chunks from exceeding a limit', function() {
            $items = ['aa', 'ab', 'ac', 'ba', 'bb', 'bc', 'ca', 'cb', 'cc'];
            $chunks = chunkBy(function(string $item) {
                return $item[0]; // return first char
            }, $items, 2);
            expect(toArray($chunks))->equal([
                ['aa', 'ab'], ['ac'],
                ['ba', 'bb'], ['bc'],
                ['ca', 'cb'], ['cc']
            ]);
        });
    });
    describe('compact', function() {
        docFn(compact::class);

        test('Removes all null values from an iterable', function() {
            $res = compact([1,null,2,3,null,null,4]);
            expect(toArray($res))->equal([1,2,3,4]);
        });
    });
    describe('complement', function() {
        docFn(complement::class);
        test('Returns a function that, for the same arguments, returns the same values as its argument function, but negated', function() {
            $isEven = function($n) { return $n % 2 === 0; };
            $isOdd = complement($isEven);
            expect($isOdd(3))->equal(true);
        });
        docOutro('Note: this function is an alias of the curried version of `not`');
    });
    describe('compose', function() {
        docFn(compose::class);

        test('Composes functions together. compose(f, g)(x) == f(g(x))', function() {
            $mul2 = Curried\op('*')(2);
            $add3 = Curried\op('+')(3);

            $add3ThenMul2 = compose($mul2, $add3);
            $res = $add3ThenMul2(5);
            expect($res)->equal(16);
        });
        test('Allows an empty initial argument', function() {
            $res = compose(
                Curried\reduce(function($acc, $v) { return $acc + $v; }, 0),
                function() { yield from [1,2,3]; }
            )();
            expect($res)->equal(6);
        });
    });
    describe('construct', function() {
        docFn(construct::class);
        test('Constructs (instantiates) a new class with the given arguments', function() {
            $res = construct(\ArrayObject::class, [1,2,3]);
            expect($res->count())->equal(3);
        });
    });
    describe('curry', function() {
        docFn(curry::class);

        test('currys the given function $n times', function() {
            $res = curry(_idArgs::class, 2)(1)(2)(3);
            expect($res)->equal([1,2,3]);
        });
        docOutro('Given a function definition: (a, b) -> c. A curried version will look like (a) -> (b) -> c');
    });
    describe('differenceWith', function() {
        docFn(differenceWith::class);
        test('Takes the difference between two iterables with a given comparator', function() {
            $res = differenceWith(partial(op, '==='), [1,2,3,4,5], [2,3,4]);
            expect(toArray($res))->equal([1,5]);
        });
    });
    describe('dd', function() {
        docFn(dd::class);
        test('dumps and dies', function() {
            $res = null;
            $died = false;
            $dump = function($v) use (&$res) {$res = $v;};
            $die = function() use (&$died) { $died = true; };
            dd(1, $dump, $die);
            expect($res)->equal(1);
            expect($died)->equal(true);
        });
    });
    describe('drop', function() {
        docFn(drop::class);
        test('Drops the first num items from an iterable', function() {
            $res = drop(2, range(0, 3));
            expect(toArray($res))->equal([2, 3]);
        });
    });
    describe('dropWhile', function() {
        docFn(dropWhile::class);
        test('Drops elements from the iterable while the predicate returns true', function() {
            $res = dropWhile(Curried\op('>')(0), [2, 1, 0, 1, 2]);
            expect(toArray($res))->equal([0, 1, 2]);
        });
    });
    describe('each', function() {
        docFn(each::class);
        test('Invokes a callable on each item in an iterable', function() {
            $state = [
                (object) ['id' => 1],
                (object) ['id' => 2],
            ];
            each(function($item) {
                $item->id += 1;
            }, $state);

            expect([$state[0]->id, $state[1]->id])->equal([2,3]);
        });
        docOutro('Normally using php foreach should suffice for iterating over an iterable; however, php variables in foreach loops are not scoped whereas closures are.');
    });
    describe('filter', function() {
        docFn(filter::class);
        it('Lazily filters an iterable off of a predicate that should return true or false. If true, keep the data, else remove the data from the iterable', function() {
            $values = filter(partial(op, '>', 2), [1,2,3,4]); // keep all items that are greater than 2
            expect(toArray($values))->equal([3,4]);
        });
    });
    describe('filterKeys', function() {
        docFn(filterKeys::class);
        test('Filters an iterable off of the keys', function() {
            $res = filterKeys(Curried\inArray(['a', 'b']), [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]);
            expect(toArrayWithKeys($res))->equal(['a' => 1, 'b' => 2]);
        });
    });
    describe('flatMap', function() {
        docFn(flatMap::class);

        test('Maps and then flattens an iterable', function() {
            $res = flatMap(function($v) {
                return [-$v, $v];
            }, range(1, 3));

            expect(toArray($res))->equal([-1, 1, -2, 2, -3, 3]);
        });

        docOutro('flatMap is perfect for when you want to map an iterable and also add elements to the resulting iterable.');
    });
    describe('flatten', function() {
        docFn(flatten::class);
        test('Flattens nested iterables into a flattened set of elements', function() {
            $res = flatten([1, [2, [3, [4]]]]);
            expect(toArray($res))->equal([1,2,3,4]);
        });
        test('Can flatten a specific number of levels', function() {
            $res = flatten([1,[2, [3]]], 1);
            expect(toArray($res))->equal([1, 2, [3]]);
        });
        test('Flattening zero levels does nothing', function() {
            $res = flatten([1, [2]], 0);
            expect(toArray($res))->equal([1,[2]]);
        });
    });
    describe('flip', function() {
        docFn(flip::class);
        test('Flips the keys => values of an iterable to values => keys', function() {
            $res = flip(['a' => 0, 'b' => 1]);
            expect(toArray($res))->equal(['a', 'b']);
        });
    });
    describe('fromPairs', function() {
        docFn(fromPairs::class);
        test('Converts an iterable of tuples [$key, $value] into an associative iterable', function() {
            $res = fromPairs([
                ['a', 1],
                ['b', 2]
            ]);
            expect(toArrayWithKeys($res))->equal([
                'a' => 1,
                'b' => 2
            ]);
        });
    });
    describe('groupBy', function() {
        docFn(groupBy::class);
        docIntro('Alias of chunkBy');
        test('Groups items together off of the result from the callable', function() {
            $items = ['aa', 'ab', 'ac', 'ba', 'bb', 'bc', 'ca', 'cb', 'cc'];
            $groupedItems = groupBy(function(string $item) {
                return $item[0]; // return first char
            }, $items);
            expect(toArray($groupedItems))->equal([
                ['aa', 'ab', 'ac'],
                ['ba', 'bb', 'bc'],
                ['ca', 'cb', 'cc']
            ]);
        });
        test('Allows a maxSize to prevent groups from exceeding a limit', function() {
            $items = ['aa', 'ab', 'ac', 'ba', 'bb', 'bc', 'ca', 'cb', 'cc'];
            $groupedItems = groupBy(function(string $item) {
                return $item[0]; // return first char
            }, $items, 2);
            expect(toArray($groupedItems))->equal([
                ['aa', 'ab'], ['ac'],
                ['ba', 'bb'], ['bc'],
                ['ca', 'cb'], ['cc']
            ]);
        });
    });
    describe('hasIndexIn', function() {
        docFn(hasIndexIn::class);

        test('Checks if a nested index exists in the given data', function() {
            $res = hasIndexIn(['a', 'b', 'c'], [
                'a' => ['b' => ['c' => null]]
            ]);

            expect($res)->equal(true);
        });
        test('Returns false if any of the indexes do not exist in the data', function() {
            $res = hasIndexIn(['a', 'b', 'c'], [
                'a' => ['b' => []]
            ]);

            expect($res)->equal(false);
        });
    });
    describe('head', function() {
        docFn(head::class);
        test('Returns the fist element in an iterable', function() {
            $res = head([1,2,3]);
            expect($res)->equal(1);
        });
        test('But returns null if the iterable is empty', function() {
            $res = head([]);
            expect($res)->equal(null);
        });
    });
    describe('identity', function() {
        docFn(identity::class);
        it('Returns its only argument as is', function() {
            expect(identity(1))->equal(1);
            expect(identity("foo"))->equal("foo");
        });
        docOutro('Note: this function is an alias of `id`');
    });
    describe('inArray', function() {
        docFn(inArray::class);
        it('Checks if an item is within an array of items', function() {
            $res = inArray([1,2,3], 2);
            expect($res)->equal(true);
        });
    });
    describe('index', function() {
        docFn(index::class);
        test('Accesses an index in an array', function() {
            $res = index('a', ['a' => 1]);
            expect($res)->equal(1);
        });
        test('If no value exists at the given index, $else will be returned', function() {
            $res = index('a', ['b' => 1], 2);
            expect($res)->equal(2);
        });
    });
    describe('indexIn', function() {
        docFn(indexIn::class);
        test('Accesses a nested index in a deep array structure', function() {
            $res = indexIn(['a', 'b'], ['a' => ['b' => 1]]);
            expect($res)->equal(1);
        });
        test('If any of the indexes do not exist, $else will be returned', function() {
            $res = indexIn(['a', 'b'], ['a' => ['c' => 1]], 2);
            expect($res)->equal(2);
        });
    });
    describe('indexOf', function() {
        docFn(indexOf::class);
        test('Searches for an element and returns the key if found', function() {
            $res = indexOf(partial(op, '==', 'b'), ['a', 'b', 'c']);
            expect($res)->equal(1);
        });
    });
    describe('isNull', function() {
        docFn(isNull::class);
        test('alias for is_null', function() {
            expect(isNull(null))->equal(true);
            expect(isNull(0))->equal(false);
        });
    });
    describe('iter', function() {
        docFn(iter::class);
        docIntro('Converts any iterable into a proper instance of Iterator.');
        test('Can convert arrays', function() {
            expect(iter([1,2,3]))->instanceof('Iterator');
        });
        test('Can convert an Iterator', function() {
            expect(iter(new \ArrayIterator([1,2,3])))->instanceof('Iterator');
        });
        test('Can convert objects', function() {
            $obj = (object) ['a' => 1, 'b' => 2];
            expect(iter($obj))->instanceof('Iterator');
            expect(toArrayWithKeys(iter($obj)))->equal(['a' => 1, 'b' => 2]);
        });
        test('Can convert any iterable', function() {
            $a = new class() implements \IteratorAggregate {
                public function getIterator() {
                    return new \ArrayIterator([1,2,3]);
                }
            };
            expect(iter($a))->instanceof('Iterator');
            expect(toArray(iter($a)))->equal([1,2,3]);
        });
        test('Can convert strings', function() {
            expect(iter('abc'))->instanceof('Iterator');
            expect(toArray(iter('abc')))->equal(['a', 'b', 'c']);
        });
        test('Will throw an exception otherwise', function() {
            expect(function() {
                iter(1);
            })->throw('LogicException', 'Iter could not be converted into an iterable.');
        });
    });
    describe('join', function() {
        docFn(join::class);
        test('Joins an iterable with a given separator', function() {
            $res = join(",", range(1,3));
            expect($res)->equal("1,2,3");
        });
    });
    describe('keys', function() {
        docFn(keys::class);
        test('Yields only the keys of an in iterable', function() {
            $keys = keys(['a' => 1, 'b' => 2]);
            expect(toArray($keys))->equal(['a', 'b']);
        });
    });
    describe('map', function() {
        docFn(map::class);
        it('Lazily maps an iterable\'s values to a different set', function() {
            $values = map(partial(op, '*', 2), [1,2,3,4]);
            expect(toArray($values))->equal([2,4,6,8]);
        });
    });
    describe('mapAccum', function() {
        docFn(mapAccum::class);

        test('Maps a function to each element of a list while passing in an accumulator to accumulate over every iteration', function() {
            $data = iter('abcd');
            [$totalSort, $values] = mapAccum(function($acc, $value) {
                return [$acc + 1, ['name' => $value, 'sort' => $acc]];
            }, iter('abcd'), 0);

            expect($totalSort)->equal(4);
            expect($values)->equal([
                ['name' => 'a', 'sort' => 0],
                ['name' => 'b', 'sort' => 1],
                ['name' => 'c', 'sort' => 2],
                ['name' => 'd', 'sort' => 3],
            ]);
        });

        docOutro('Note: mapAccum converts the interable into an array and is not lazy like most of the other functions in this library');
    });
    describe('mapKeys', function() {
        docFn(mapKeys::class);
        it('Lazily maps an iterable\'s keys to a different set', function() {
            $keys = mapKeys(partial(op, '.', '_'), ['a' => 1, 'b' => 2]);
            expect(toArrayWithKeys($keys))->equal(['a_' => 1, 'b_' => 2]);
        });
    });
    describe('mapKeyValue', function() {
        docFn(mapKeyValue::class);
        it('Lazily maps an iterable\'s key/value tuples to a different set', function() {
            $keys = mapKeyValue(function($kv) {
                [$key, $value] = $kv;
                return ["{$key}_", $value * $value];
            }, ['a' => 1, 'b' => 2]);
            expect(toArrayWithKeys($keys))->equal(['a_' => 1, 'b_' => 4]);
        });
    });
    describe('mapOn', function() {
        docFn(mapOn::class);
        it('Maps values on specific keys', function() {
            $values = mapOn([
                'a' => partial(op, '*', 3),
                'b' => partial(op, '+', 1),
            ], [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]);

            expect(toArray($values))->equal([3,3,3]);
        });
    });
    describe('nullable', function() {
        docFn(nullable::class);
        test('Performs the callable if the value is not null', function() {
            expect(nullable('intval', '0'))->equal(0);
        });
        test('Returns null if the value is null', function() {
            expect(nullable('intval', null))->equal(null);
        });
    });
    describe('onEach', function() {
        docFn(onEach::class);
        docIntro('Duplicate of each.');
        test('Invokes a callable on each item in an iterable', function() {
            $state = [
                (object) ['id' => 1],
                (object) ['id' => 2],
            ];
            onEach(function($item) {
                $item->id += 1;
            }, $state);

            expect([$state[0]->id, $state[1]->id])->equal([2,3]);
        });
        docOutro('Normally using php foreach should suffice for iterating over an iterable; however, php variables in foreach loops are not scoped whereas closures are.');
    });
    describe('op', function() {
        docFn(op::class);

        $intro = <<<'INTRO'
op evaluates binary operations. It expects the right hand operator first which makes most sense when currying or partially applying the op function.
When reading the op func, it should be read: `evaluate $op with $b with $a` e.g.:

```
op('+', 2, 3) -> add 2 with 3
op('-', 2, 3) -> subtract 2 from 3
op('>', 2, 3) => compare greater than 2 with 3
```
INTRO;
        docIntro($intro);
        test('Evaluates two values with a given operator', function() {
            $res = op('<', 2, 1);
            expect($res)->equal(true);
        });
        test('Supports equality operators', function() {
            $obj = new stdClass();
            $ops = [
                ['==', [1, 1]],
                ['eq', [2, 2]],
                ['!=', [1, 2]],
                ['neq', [2, 3]],
                ['===', [$obj, $obj]],
                ['!==', [new stdClass(), new stdClass()]],
                ['>', [1, 2]],
                ['gt', [1, 3]],
                ['>=', [1, 2]],
                ['gte', [1, 1]],
                ['<', [2, 1]],
                ['lt', [3, 1]],
                ['<=', [2, 1]],
                ['lte', [1, 1]],
            ];

            foreach ($ops as list($op, list($b, $a))) {
                $res = op($op, $b, $a);
                expect($res)->equal(true);
            }
        });
        test('Supports other operators', function() {
            $ops = [
                ['+', [2, 3], 5],
                ['-', [2, 3], 1],
                ['*', [2, 3], 6],
                ['**', [2, 3], 9],
                ['/', [2, 3], 1.5],
                ['%', [2, 3], 1],
                ['.', ['b', 'a'], 'ab'],
            ];

            foreach ($ops as list($op, list($b, $a), $expected)) {
                $res = op($op, $b, $a);
                expect($res)->equal($expected);
            }
        });
        test('Is more useful partially applied or curried', function() {
            $add2 = Curried\op('+')(2);
            $mul3 = partial(op, '*', 3);
            $sub4 = Curried\op('-')(4);

            // ((2 + 2) * 3) - 4
            $res = compose($sub4, $mul3, $add2)(2);
            expect($res)->equal(8);
        });
    });
    describe('pad', function() {
        docFn(pad::class);
        test('Pads an iterable to a specific size', function() {
            $res = pad(5, [1,2,3]);
            expect(toArray($res))->equal([1,2,3,null,null]);
        });
        test('Allows custom pad values', function() {
            $res = pad(5, [1,2,3], 0);
            expect(toArray($res))->equal([1,2,3,0,0]);
        });
        test('Pads nothing if iterable is the same size as pad size', function() {
            $res = pad(5, [1,2,3,4,5]);
            expect(toArray($res))->equal([1,2,3,4,5]);
        });
        test('Pads nothing if iterable is greater than pad size', function() {
            $res = pad(5, [1,2,3,4,5,6]);
            expect(toArray($res))->equal([1,2,3,4,5,6]);
        });
        test('Ignores keys of original iterable', function() {
            $res = pad(3, ['a' => 1, 'b' => 2]);
            expect(toArrayWithKeys($res))->equal([1,2,null]);
        });
    });
    describe('partial', function() {
        docFn(partial::class);
        test('Partially applies arguments to a function. Given a function signature like f = (a, b, c) -> d, partial(f, a, b) -> (c) -> d', function() {
            $fn = function($a, $b, $c) {
                return ($a + $b) * $c;
            };
            $fn = partial($fn, 1, 2); // apply the two arguments (a, b) and return a new function with signature (c) -> d
            expect($fn(3))->equal(9);
        });
        test('You can also use place holders when partially applying', function() {
            $fn = function($a, $b, $c) { return ($a + $b) * $c; };

            // _() represents a placeholder for parameter b.
            $fn = partial($fn, 1, _(), 3); // create the new func with signature (b) -> d

            expect($fn(2))->equal(9);
        });
        test('Full partial application also works', function() {
            $fn = function($a, $b) { return [$a, $b]; };
            $fn = partial($fn, 1, 2);
            expect($fn())->equal([1,2]);
        });
    });
    describe('partition', function() {
        docFn(partition::class);

        it('Splits an iterable into different arrays based off of a predicate. The predicate should return the index to partition the data into', function() {
            list($left, $right) = partition(function($v) {
                return $v < 3 ? 0 : 1;
            }, [1,2,3,4]);

            expect([$left, $right])->equal([[1,2], [3,4]]);
        });
    });
    describe('pick', function() {
        docFn(pick::class);        
        test('Picks only the given fields from a structured array', function() {
            $res = pick(['a', 'b'], [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]);
            expect($res)->equal(['a' => 1, 'b' => 2]);
        });
        test('Can be used in curried form', function() {
            $res = arrayMap(Curried\pick(['id', 'name']), [
                ['id' => 1, 'name' => 'Foo', 'slug' => 'foo'],
                ['id' => 2, 'name' => 'Bar', 'slug' => 'bar'],
            ]);
            expect($res)->equal([
                ['id' => 1, 'name' => 'Foo'],
                ['id' => 2, 'name' => 'Bar'],
            ]);
        });
    });
    describe('pickBy', function() {
        docFn(pickBy::class);        
        test('Picks only the fields that match the pick function from a structured array', function() {
            $res = pickBy(Curried\spread(function(string $key, int $value): bool {
                return $value % 2 === 0;
            }), [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]);
            expect($res)->equal(['b' => 2]);
        });
    });
    describe('pipe', function() {
        docFn(pipe::class);

        test('Creates a function that pipes values from one func to the next.', function() {
            $add3 = Curried\op('+')(3);
            $mul2 = Curried\op('*')(2);

            $add3ThenMul2 = pipe($add3, $mul2);
            $res = $add3ThenMul2(5);
            expect($res)->equal(16);
        });
        test('Allows an empty initial argument', function() {
            $res = pipe(
                function() { yield from [1,2,3]; },
                Curried\reduce(function($acc, $v) { return $acc + $v; }, 0)
            )();
            expect($res)->equal(6);
        });

        docOutro('`pipe` and `compose` are sister functions and do the same thing except the functions are composed in reverse order. pipe(f, g)(x) = g(f(x))');
    });
    describe('product', function() {
        docFn(product::class);

        test('Creates a cartesian product of multiple sets', function() {
            $res = product([1,2], [3,4], [5, 6]);
            expect(toArray($res))->equal([
                [1,3,5],
                [1,3,6],
                [1,4,5],
                [1,4,6],
                [2,3,5],
                [2,3,6],
                [2,4,5],
                [2,4,6],
            ]);
        });
    });
    describe('prop', function() {
        docFn(prop::class);

        test('Accesses a property from an object', function() {
            $obj = new \StdClass();
            $obj->id = 1;
            $res = prop('id', $obj);
            expect($res)->equal(1);
        });
        test('If no property exists, it will return the $else value', function() {
            $obj = new \StdClass();
            $res = prop('id', $obj, 2);
            expect($res)->equal(2);
        });
    });
    describe('propIn', function() {
        docFn(propIn::class);

        test('Accesses a property deep in an object tree', function() {
            $obj = new \StdClass();
            $obj->id = 1;
            $obj->child = new \StdClass();
            $obj->child->id = 2;
            $res = propIn(['child', 'id'], $obj);
            expect($res)->equal(2);
        });
        test('If any property is missing in the tree, it will return the $else value', function() {
            $obj = new \StdClass();
            $obj->id = 1;
            $obj->child = new \StdClass();
            $res = propIn(['child', 'id'], $obj, 3);
            expect($res)->equal(3);
        });
    });
    describe('range', function() {
        docFn(range::class);
        test('Creates an iterable of a range of values starting from $start going to $end inclusively incrementing by $step', function() {
            $res = range(1, 3);
            expect(toArray($res))->equal([1,2,3]);
        });
        test('It also allows a decreasing range', function() {
            $res = range(3, 1);
            expect(toArray($res))->equal([3,2,1]);
        });
        test('An exception will be thrown if the $step provided goes in the wrong direction', function() {
            expect(function() {
                toArray(range(1, 2, -1));
            })->throw(\InvalidArgumentException::class);
            expect(function() {
                toArray(range(2, 1, 1));
            })->throw(\InvalidArgumentException::class);
        });
    });
    describe('reduce', function() {
        docFn(reduce::class);
        test('Reduces an iterable into a single value', function() {
            $res = reduce(function($acc, $v) {
                return $acc + $v;
            }, range(1,3), 0);
            expect($res)->equal(6);
        });
    });
    describe('reduceKeyValue', function() {
        docFn(reduceKeyValue::class);
        test('Reduces an iterables key value pairs into a value', function() {
            $res = reduceKeyValue(function($acc, $kv) {
                [$key, $value] = $kv;
                return $acc . $key . $value;
            }, fromPairs([['a', 1], ['b', 2]]), "");
            expect($res)->equal("a1b2");
        });
    });
    describe('reindex', function() {
        docFn(reindex::class);
        test('Re-indexes a collection via a callable', function() {
            $res = reindex(function($v) {
                return $v['id'];
            }, [['id' => 2], ['id' => 3], ['id' => 1]]);

            expect(toArrayWithKeys($res))->equal([
                2 => ['id' => 2],
                3 => ['id' => 3],
                1 => ['id' => 1],
            ]);
        });
    });
    describe('retry', function() {
        docFn(retry::class);
        test('Executes a function and retries if an exception is thrown', function() {
            $i = 0;
            $res = retry(function() use (&$i) {
                $i += 1;
                if ($i <= 1) {
                    throw new \Exception('bad');
                }

                return $i;
            });

            expect($res)->equal(2);
        });
        test('Only retries $maxTries times else it gives up and bubbles the exception', function() {
            expect(function() {
                $i = 0;
                retry(function() use (&$i) {
                    $i += 1;
                    throw new \Exception((string) $i);
                }, 5);
            })->throw('Exception', '6');
        });
        test('Retries until $shouldRetry returns false', function() {
            $i = 0;

            expect(function() {
                $res = retry(function() use (&$i) {
                    $i += 1;
                    throw new \Exception((string) $i);
                }, function($numRetries, \Throwable $t = null) {
                    return $numRetries < 2;
                });
            })->throw('Exception', '2');
        });
        test("Sends numRetries into the main fn", function() {
            $res = retry(function($numRetries) {
                if (!$numRetries) {
                    throw new Exception('bad');
                }

                return $numRetries;
            }, 2);
            expect($res)->equal(1);
        });
        docOutro('Keep in mind that maxTries determines the number of *re*-tries. This means the function will execute maxTries + 1 times since the first invocation is not a retry.');
    });
    describe('search', function() {
        docFn(search::class);
        test('Searches for an element in a collection where the callable returns true', function() {
            $res = search(function($v) {
                return $v['id'] == 2;
            }, [['id' => 1], ['id' => 2], ['id' => 3]]);
            expect($res)->equal(['id' => 2]);
        });
        test('Returns null if no element was found', function() {
            $res = search(function($v) {
                return false;
            }, [['id' => 1], ['id' => 2], ['id' => 3]]);
            expect($res)->equal(null);
        });
    });
    describe('setIndex', function() {
        docFn(setIndex::class);
        test('Sets an index in an array', function() {
            $res = setIndex('a', 1, []);
            expect($res['a'])->equal(1);
        });
    });
    describe('setIndexIn', function() {
        docFn(setIndexIn::class);
        test('Sets a nested index in an array', function() {
            $res = setIndexIn(['a', 'b'], 1, ['a' => []]);
            expect($res['a']['b'])->equal(1);
        });
    });
    describe('setProp', function() {
        docFn(setProp::class);
        test('Sets a property in an object', function() {
            $res = setProp('a', 1, (object) []);
            expect($res->a)->equal(1);
        });
    });
    describe('slice', function() {
        docFn(slice::class);
        test('It takes an inclusive slice from start to a given length of an interable', function() {
            $sliced = slice(1, range(0, 4), 2);
            expect(toArray($sliced))->equal([1, 2]);
        });
        test('If length is not supplied it default to the end of the iterable', function() {
            $sliced = slice(2, range(0, 4));
            expect(toArray($sliced))->equal([2,3,4]);
        });
        test('will not consume the iterator once the slice has been yielded', function() {
            $i = 0;
            $gen = function() use (&$i) {
                foreach (range(0, 4) as $v) {
                    $i = $v;
                    yield $i;
                }
            };
            $sliced = toArray(slice(1, $gen(), 2));
            expect($sliced)->equal([1, 2]);
            expect($i)->equal(2);
        });
    });
    describe('sortFromArray', function() {
        docFn(sortFromArray::class);
        test("Sort an iterable with a given array of ordered elements to sort by", function() {
            $data = [
                ['id' => 1, 'name' => 'A'],
                ['id' => 2, 'name' => 'B'],
                ['id' => 3, 'name' => 'C'],
            ];
            $res = sortFromArray(Curried\index('id'), [2,3,1], $data);
            expect(arrayMap(Curried\index('name'), $res))->equal(['B', 'C', 'A']);
        });
        test('Throws an exception if any item in the iterable is not within the orderedElements', function() {
            expect(function() {
                $data = [['id' => 1]];
                $res = sortFromArray(Curried\index('id'), [], $data);
            })->throw(\LogicException::class, 'Cannot sort element key 1 because it does not exist in the ordered elements.');
        });

        docOutro("I've found this to be very useful when you fetch records from a database with a WHERE IN clause, and you need to make sure the results are in the same order as the ids in the WHERE IN clause.");
    });
    describe('spread', function() {
        docFn(spread::class);
        test("Spreads an array of arguments to a callable", function() {
            $res = spread(function($a, $b) {
                return $a . $b;
            }, ['a', 'b']);
            expect($res)->equal('ab');
        });
        test('Can be used in the curried form to unpack tuple arguments', function() {
            $res = arrayMap(Curried\spread(function(string $first, int $second) {
                return $first . $second;
            }), [['a', 1], ['b', 2]]);
            expect($res)->equal(['a1', 'b2']);
        });
        docOutro("Note: this is basically just an alias for `call_user_func_array` or simply a functional wrapper around the `...` (spread) operator.");
    });
    describe('take', function() {
        docFn(take::class);
        test('Takes the first num items from an iterable', function() {
            $res = take(2, range(0, 10));
            expect(toArray($res))->equal([0, 1]);
        });
    });
    describe('takeWhile', function() {
        docFn(takeWhile::class);
        test('Takes elements from an iterable while the $predicate returns true', function() {
            $res = takeWhile(Curried\op('>')(0), [2, 1, 0, 1, 2]);
            expect(toArray($res))->equal([2,1]);
        });
    });
    describe('toArray', function() {
        docFn(toArray::class);
        it('will tranform any iterable into an array', function() {
            $res = toArray((function() { yield 1; yield 2; yield 3; })());
            expect($res)->equal([1,2,3]);
        });
        it('can also be used as a constant', function() {
            $res = compose(toArray, identity)((function() {yield 1; yield 2; yield 3;})());
            expect($res)->equal([1,2,3]);
        });
    });
    describe('toArrayWithKeys', function() {
        docFn(toArrayWithKeys::class);
        it('can convert to an array and keep the keys', function() {
            $gen = function() { yield 'a' => 1; yield 'b' => 2; };
            expect(toArrayWithKeys($gen()))->equal(['a' => 1, 'b' => 2]);
        });
    });
    describe('toPairs', function() {
        docFn(toPairs::class);
        test('Transforms an associative array into an iterable of tuples [$key, $value]', function() {
            $res = toPairs([
                'a' => 1,
                'b' => 2,
            ]);
            expect(toArray($res))->equal([
                ['a', 1],
                ['b', 2]
            ]);
        });
    });
    describe('updateIndexIn', function() {
        docFn(updateIndexIn::class);

        test('Updates a nested element within a deep array structure', function() {
            $data = ['a' => ['b' => ['c' => 3]]];

            $data = updateIndexIn(['a', 'b', 'c'], function($v) {
                return $v * $v;
            }, $data);

            expect($data)->equal(['a' => ['b' => ['c' => 9]]]);
        });
        test('Throws an exception if nested key does not exist', function() {
            expect(function() {
                $data = ['a' => ['b' => ['c' => 9]]];
                updateIndexIn(['a', 'c', 'c'], function() {}, $data);
            })->throw(\RuntimeException::class, 'Could not updateIn because the keys a -> c -> c could not be found.');
        });
    });
    describe('values', function() {
        docFn(values::class);
        test('Exports only the values of an iterable', function() {
            $res = values(['a' => 1, 'b' => 2]);
            expect(toArrayWithKeys($res))->equal([1,2]);
        });
    });
    describe('when', function() {
        docFn(when::class);
        it('Evaluates the given value with the $then callable if the predicate returns true', function() {
            $if = function($v) { return $v == 3; };
            $then = function($v) { return $v * $v; };
            $res = when($if, $then, 3);
            expect($res)->equal(9);
        });
        test('But will return the given value if the predicate returns false', function() {
            $if = function($v) { return $v == 3; };
            $then = function($v) { return $v * $v; };
            $res = when($if, $then, 4);
            expect($res)->equal(4);
        });
    });
    describe('withState', function() {
        docFn(withState::class);

        test('Decorate a function with accumulating state', function() {
            $fn = withState(function($state, $v) {
                return [$state + 1, $state . ': ' . $v];
            }, 1);
            $res = arrayMap($fn, iter('abcd'));
            expect($res)->equal(['1: a', '2: b', '3: c', '4: d']);
        });
    });
    describe('within', function() {
        docFn(within::class);

        test('Only allows keys within the given array to stay', function() {
            $data = flip(iter('abcd'));
            $res = within(['a', 'c'], $data);
            expect(toArrayWithKeys($res))->equal(['a' => 0, 'c' => 2]);
        });
    });
    describe('without', function() {
        docFn(without::class);

        test('Filters an iterable to be without the given keys', function() {
            $data = flip(iter('abcd'));
            $res = without(['a', 'c'], $data);
            expect(toArrayWithKeys($res))->equal(['b' => 1, 'd' => 3]);
        });
    });
    describe('zip', function() {
        docFn(zip::class);
        test('Zips multiple iterables into an iterable n-tuples', function() {
            $res = zip(iter('abc'), range(1,3), [4,5,6]);
            expect(toArray($res))->equal([
                ['a', 1, 4],
                ['b', 2, 5],
                ['c', 3, 6],
            ]);
        });
        test('Returns an empty iterable if no iters are present', function() {
            expect(toArray(zip()))->equal([]);
        });
    });
});
