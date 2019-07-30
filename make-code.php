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
use function Krak\Fun\{
    toArray, map, filter, search, partition, andf, chain,
    Generate\createDocComment,
    Generate\curryFunction,
    Generate\createConstFromFunc
};
use function Krak\Fun\Curried\{inArray, isInstance, trans, prop};

require_once __DIR__ . '/vendor/autoload.php';


function main() {
    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

    try {
        $ast = $parser->parse(file_get_contents(__DIR__ . '/src/fn.php'));
        $ast[0]->setDocComment(createDocComment());

        $ns = $ast[0];
        $originalStmts = $ns->stmts;

        $constStmts = filter(isInstance(Function_::class), $originalStmts);
        $constStmts = toArray(map(function($stmt) use ($ns) {
            return createConstFromFunc($stmt, $ns);
        }, $constStmts));

        $curriedStmts = filter(andf(
            isInstance(Function_::class),
            'Krak\Fun\Generate\isCurryable'
        ), $originalStmts);
        $curriedStmts = toArray(map(function($stmt) {
            return curryFunction($stmt);
        }, $curriedStmts));

        $ast = [$ns];
    } catch (Error $error) {
        echo "Parse error: {$error->getMessage()}\n";
        return;
    }

    $pp = new PrettyPrinter\Standard();

    $ns->name->parts[] = 'Curried';
    $ns->stmts = toArray($curriedStmts);
    file_put_contents(__DIR__ . '/src/curried.generated.php', $pp->prettyPrintFile($ast));

    $ast[0]->name->parts[2] = 'c';
    $ast[0]->stmts = toArray(chain($curriedStmts, $constStmts));
    file_put_contents(__DIR__ . '/src/c.generated.php', $pp->prettyPrintFile($ast));

    $ast[0]->name->parts[2] = 'f';
    $ast[0]->stmts = toArray($originalStmts);
    file_put_contents(__DIR__ . '/src/f.generated.php', $pp->prettyPrintFile($ast));

    $ast[0]->name->parts[2] = 'Consts';
    $ast[0]->stmts = toArray($constStmts);
    file_put_contents(__DIR__ . '/src/consts.ns.generated.php', $pp->prettyPrintFile($ast));

    unset($ast[0]->name->parts[2]);
    $ast[0]->stmts = toArray($constStmts);
    file_put_contents(__DIR__ . '/src/consts.generated.php', $pp->prettyPrintFile($ast));
}

main();
