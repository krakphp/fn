<?php

use function Krak\Fn\{toArray, curry, partial, compose};
use const Krak\Fn\{toArray};

use Lavoiesl\PhpBenchmark\Benchmark;

require_once __DIR__ . '/../vendor/autoload.php';

function mapCurried(callable $predicate) {
    return function($data) use ($predicate) {
        foreach ($data as $key => $val) {
            yield $key => $predicate($val);
        }
    };
}

function mapAutoCurried(...$args) {
    return autoCurry($args, 2, function(callable $predicate, $data) {
        foreach ($data as $key => $val) {
            yield $key => $predicate($val);
        }
    });
}

function mapAutoCurriedStatic(...$args) {
    switch (count($args)) {
    case 1:
        return map1($args[0]);
    default:
        return map2(...$args);
    }
}

function map1(callable $predicate) {
    return function($data) use ($predicate) {
        return map2($predicate, $data);
    };
}
function map2(callable $predicate, $data) {
    foreach ($data as $key => $val) {
        yield $key => $predicate($val);
    }
}

function autoCurry(array $args, $numArgs, callable $fn) {
    if (count($args) >= $numArgs) {
        return $fn(...$args);
    }
    if (count($args) == $numArgs - 1) {
        return partial($fn, ...$args);
    }
    if (count($args) == 0) {
        return curry($fn, $numArgs - 1);
    }

    return curry(
        partial($fn, ...$args),
        ($numArgs - 1 - count($args))
    );
}

function plusOne() {
    static $fn;
    $fn = $fn ?: function($v) {
        return $v + 1;
    };
    return $fn;
}

$bm = new Benchmark();
$bm->add('curried', function() {
    $res = compose(toArray, mapCurried(plusOne()))([1,2,3]);
});
// $bm->add('auto-curried', function() {
//     $res = compose(toArray, mapAutoCurried(plusOne()))([1,2,3]);
// });
// $bm->add('auto-curried-no-curry', function() {
//     $res = toArray(mapAutoCurried(plusOne(), [1,2,3]));
// });
$bm->add('auto-curried-static', function() {
    $res = compose(toArray, mapAutoCurriedStatic(plusOne()))([1,2,3]);
});
// $bm->add('auto-curried-static-no-curry', function() {
//     $res = toArray(mapAutoCurriedStatic(plusOne(), [1,2,3]));
// });
$bm->run();
