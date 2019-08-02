<?php

use Krak\Fun\{f, c};
use Krak\{Fun as _f, Fun\Curried as _c};

describe('Fun Api', function() {
    it('can utilize the generated api helpers', function() {
        $res = f\compose(
            c\join(','),
            c\toArray,
            c\map(c\op('*')(2))
        )([1,2,3,4]);
        expect($res)->equal('2,4,6,8');
    });
    it('can utilize the pre-1.0 generated api helpers', function() {
        $res = _f\compose(
            _c\join(','),
            _f\toArray,
            _c\map(_c\op('*')(2))
        )([1,2,3,4]);
        expect($res)->equal('2,4,6,8');
    });
    it('can run the home page example', function() {
        $res = f\compose(
            c\toArray,
            c\map(function($tup) {
                return $tup[0] + $tup[1];
            }),
            c\toPairs
        )([1,2,3]);
        expect($res)->equal([1,3,5]);
    });
});
