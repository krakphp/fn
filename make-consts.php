<?php

use PhpParser\{
    ParserFactory, NodeDumper, Error, Comment,
    Node\Stmt\Function_,
    Node\Stmt\Namespace_,
    Node\Stmt,
    Node\Const_,
    Node\Expr\Variable,
    PrettyPrinter,
    BuilderHelpers
};
use function Krak\Fn\{
    toArray, map, filter,
    Generate\createConstFromFunc,
    Generate\createDocComment
};
use function Krak\Fn\Curried\{isInstance};

require_once __DIR__ . '/vendor/autoload.php';

function main() {
    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

    try {
        $ast = $parser->parse(file_get_contents(__DIR__ . '/src/fn.php'));
        $ast[0]->setDocComment(createDocComment());

        $ns = $ast[0];

        $stmts = filter(isInstance(Function_::class), $ns->stmts);
        $stmts = map(function($stmt) use ($ns) {
            return createConstFromFunc($stmt, $ns);
        }, $stmts);
        $ns->stmts = toArray($stmts);

        $ast = [$ns];
    } catch (Error $error) {
        echo "Parse error: {$error->getMessage()}\n";
        return;
    }

    $pp = new PrettyPrinter\Standard();
    file_put_contents(__DIR__ . '/src/consts.generated.php', $pp->prettyPrintFile($ast));

}

main();
