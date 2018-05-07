<?php

use Krak\{Fn as f, Fn\Curried as c};

use Lavoiesl\PhpBenchmark\Benchmark;

require_once __DIR__ . '/../vendor/autoload.php';

function sortFromArrayKSort(callable $fn, array $orderedElements, iterable $iter) {
    $data = [];
    $flippedElements = \array_flip($orderedElements);

    foreach ($iter as $value) {
        $key = $fn($value);
        $data[$flippedElements[$key]] = $value;
    }

    ksort($data);
    return $data;
}

function sortFromArraySearch(callable $fn, array $orderedElements, array $iter) {
    return f\arrayMap(function($el) use ($fn, $iter) {
        return f\search(function($v) use ($fn, $el) {
            return $fn($v) == $el;
        }, $iter);
    }, $orderedElements);
}

$ids = f\toArray(f\range(1, 1000));
$data = f\arrayMap(function($id) {
    return ['id' => $id, 'name' => $id];
}, $ids);
$orderedElements = $ids;
shuffle($orderedElements);
$fn = c\index("id");

// dump(sortFromArrayKSort($fn, $orderedElements, $data));
// dump(sortFromArraySearch($fn, $orderedElements, $data));

$bm = new Benchmark();
$bm->add('sortFromArrayKSort', function() use ($data, $orderedElements, $fn) {
    $res = sortFromArrayKSort($fn, $orderedElements, $data);
});

$bm->add('sortFromArraySearch', function() use ($data, $orderedElements, $fn) {
    $res = sortFromArraySearch($fn, $orderedElements, $data);
});
$bm->run();
