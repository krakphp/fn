<?php

namespace Krak\Fn;

use const krak\fn\{toArray, id, op};

function _idArgs(...$args) {
    return $args;
}

describe('Fn', function() {
    describe('curry', function() {
        it('returns curried functions', function() {
            $res = curry(_idArgs::class, 2)(1)(2)(3);
            expect($res)->equal([1,2,3]);
        });
    });
    describe('partial', function() {
        it('can partially apply a function', function() {
            $fn = partial(_idArgs::class, 1, 2);
            expect($fn(3))->equal([1,2,3]);
        });
        it('can partially apply a function with placeholders', function() {
            $fn = partial(_idArgs::class, 1, _(), 3);
            expect($fn(2))->equal([1,2,3]);
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
    describe('toArrayWithKeys', function() {
        it('can convert to an array', function() {
            expect(toArrayWithKeys(['a' => 1, 'b' => 2]))->equal(['a' => 1, 'b' => 2]);
        });
    });
    describe('toArray', function() {
        it('can be used as a constant', function() {
            $res = compose(toArray, id)((function() {yield 1; yield 2; yield 3;})());
            expect($res)->equal([1,2,3]);
        });
    });
    describe('partition', function() {
        it('can split data', function() {
            list($left, $right) = partition(function($v) {
                return $v < 3 ? 0 : 1;
            }, [1,2,3,4]);

            expect([$left, $right])->equal([[1,2], [3,4]]);
        });
    });
    describe('filter', function() {
        it('filters data off of a predicate', function() {
            $values = toArray(filter(partial(op, '>', 2), [1,2,3,4]));
            expect($values)->equal([3,4]);
        });
    });
    it('can perform functional operations', function() {
        $res = compose(toArray, Curried\map(partial(op, '*', 3)), Curried\filter(partial(op, '>', 2)))([1,2,3,4]);
        expect($res)->equal([9, 12]);
    });
});

