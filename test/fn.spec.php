<?php

namespace Krak\Fn;

use const krak\fn\{toArray, id, op};

function _idArgs(...$args) {
    return $args;
}

describe('Fn', function() {
    describe('curry', function() {
        docFn(curry::class);

        test('currys the given function $n times', function() {
            $res = curry(_idArgs::class, 2)(1)(2)(3);
            expect($res)->equal([1,2,3]);
        });
        docOutro('Given a function definition: (a, b) -> c. A curried version will look like (a) -> (b) -> c');
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
    describe('partition', function() {
        docFn(partition::class);

        it('Splits an iterable into different arrays based off of a predicate. The predicate should return the index to partition the data into', function() {
            list($left, $right) = partition(function($v) {
                return $v < 3 ? 0 : 1;
            }, [1,2,3,4]);

            expect([$left, $right])->equal([[1,2], [3,4]]);
        });
    });
    describe('filter', function() {
        docFn(filter::class);
        it('Lazily filters an iterable off of a predicate that should return true or false. If true, keep the data, else remove the data from the iterable', function() {
            $values = toArray(filter(partial(op, '>', 2), [1,2,3,4])); // keep all items that are greater than 2
            expect($values)->equal([3,4]);
        });
    });
    describe('map', function() {
        docFn(map::class);
        it('Lazily maps an iterable\'s values to a different set', function() {
            $values = toArray(map(partial(op, '*', 2), [1,2,3,4]));
            expect($values)->equal([2,4,6,8]);
        });
    });
    describe('inArray', function() {
        docFn(inArray::class);
        it('Checks if an item is within an array of items', function() {
            $res = inArray([1,2,3], 2);
            expect($res)->equal(true);
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
    it('can perform functional operations', function() {
        $res = compose(toArray, Curried\map(partial(op, '*', 3)), Curried\filter(partial(op, '>', 2)))([1,2,3,4]);
        expect($res)->equal([9, 12]);
    });
});

