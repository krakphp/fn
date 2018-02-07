<?php

use PhpParser\{
    ParserFactory, NodeDumper, Error,
    Node\Stmt\Function_,
    Node\Stmt\Return_,
    Node\Expr\Closure,
    Node\Expr\ClosureUse,
    Node\Expr\Variable,
    PrettyPrinter
};
use function Krak\Fn\{
    toArray, map, filter, search, partition, andf,
    Generate\createDocComment,
    Generate\curryFunction
};
use function Krak\Fn\Curried\{inArray, isInstance, trans, prop};

require_once __DIR__ . '/vendor/autoload.php';



function main() {
    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

    try {
        $ast = $parser->parse(file_get_contents(__DIR__ . '/src/fn.php'));
        $ast[0]->setDocComment(createDocComment());

        $ns = $ast[0];

        $stmts = filter(andf(
            isInstance(Function_::class),
            'Krak\Fn\Generate\isCurryable'
        ), $ns->stmts);

        $stmts = map(function($stmt) {
            return curryFunction($stmt);
        }, $stmts);

        $ns->name->parts[] = 'Curried';
        $ns->stmts = toArray($stmts);

        $ast = [$ns];
    } catch (Error $error) {
        echo "Parse error: {$error->getMessage()}\n";
        return;
    }

    $pp = new PrettyPrinter\Standard();
    file_put_contents(__DIR__ . '/src/curried.generated.php', $pp->prettyPrintFile($ast));
}

main();
