<?php

namespace Krak\Fn;

use const krak\fn\{toArray, id, op};
use stdClass;

function _idArgs(...$args) {
    return $args;
}

describe('Fn', function() {
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
    describe('curry', function() {
        docFn(curry::class);

        test('currys the given function $n times', function() {
            $res = curry(_idArgs::class, 2)(1)(2)(3);
            expect($res)->equal([1,2,3]);
        });
        docOutro('Given a function definition: (a, b) -> c. A curried version will look like (a) -> (b) -> c');
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
    describe('map', function() {
        docFn(map::class);
        it('Lazily maps an iterable\'s values to a different set', function() {
            $values = map(partial(op, '*', 2), [1,2,3,4]);
            expect(toArray($values))->equal([2,4,6,8]);
        });
    });
    describe('onEach', function() {
        docFn(onEach::class);
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
        test('Supports arithmetic operators', function() {
            $ops = [
                ['+', [2, 3], 5],
                ['-', [2, 3], 1],
                ['*', [2, 3], 6],
                ['**', [2, 3], 9],
                ['/', [2, 3], 1.5],
                ['%', [2, 3], 1]
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
            $res = compose(toArray, id)((function() {yield 1; yield 2; yield 3;})());
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
});
