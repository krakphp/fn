<?php

use Lavoiesl\PhpBenchmark\Benchmark;

require_once __DIR__ . '/../vendor/autoload.php';

function namedClosure(string $name, callable $fn) {
    return function(...$args) use ($name, $fn) {
        try {
            return $fn(...$args);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Exception in closure: ' . $name, 0, $e);
        }
    };
}

function sumCurriedStandard(int $a) {
    return function(int $b) use ($a) {
        return function(int $c) use ($a, $b) {
            return $a + $b + $c;
        };
    };
}

function sumCurriedClass(int $a) {
    return new SumCurriedArg2($a);
}

final class SumCurriedArg2 {
    private $a;
    public function __construct(int $a) {
        $this->a = $a;
    }

    public function __invoke(int $b) {
        return new SumCurriedArg3($this->a, $b);
    }
}

final class SumCurriedArg3 {
    private $a;
    private $b;
    public function __construct(int $a, int $b) {
        $this->a = $a;
        $this->b = $b;
    }

    public function __invoke(int $c) {
        return $this->a + $this->b + $c;
    }
}

function sumCurriedClassStateMachine(int $a) {
    return new SumCurried($a);
}

final class SumCurried {
    private $a;
    private $b;
    private $arg = 1;

    public function __construct(int $a) {
        $this->a = $a;
    }

    public function __invoke(...$args) {
        if ($this->arg === 1) {
            $this->arg = 2;
            $this->arg2(...$args);
            return $this;
        } else if ($this->arg === 2) {
            return $this->arg3(...$args);
        }
    }

    private function arg2(int $b) {
        $this->b = $b;
    }

    private function arg3(int $c) {
        return $this->a + $this->b + $c;
    }
}

function sumCurriedNamed(int $a) {
    return namedClosure('sumCurriedArg_b', function(int $b) use ($a) {
        return namedClosure('sumCurriedArg_c', function(int $c) use ($a, $b) {
            return $a + $b + $c;
        });
    });
}

sumCurriedClass(1)(2)(null);

$bm = new Benchmark();
$bm->add('standard', function() {
    sumCurriedStandard(1)(2)(3);
});

$bm->add('class', function() {
    sumCurriedClass(1)(2)(3);
});

$bm->add('classSM', function() {
    sumCurriedClassStateMachine(1)(2)(3);
});

$bm->add('named', function() {
    sumCurriedNamed(1)(2)(3);
});

$bm->run();
