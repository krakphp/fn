<?php

namespace Krak\Fn\Generate;

use PhpParser\{
    Comment,
    Node\Stmt\Function_,
    Node\Stmt\Namespace_,
    Node\Stmt\Return_,
    Node\Expr\Closure,
    Node\Expr\ClosureUse,
    Node\Expr\Variable,
    Node\Const_,
    Node\Stmt,
    BuilderHelpers
};
use function Krak\Fn\{
    toArray, filter, partition
};

function createDocComment() {
    return new Comment\Doc("/* This file is was automatically generated. */");
}

function createConstFromFunc(Function_ $fn, Namespace_ $ns) {
    $constVal = $ns->name . '\\' . $fn->name;
    return new Stmt\Const_([
        new Const_($fn->name, BuilderHelpers::normalizeValue($constVal))
    ]);
}

function curryFunction(Function_ $fn) {
    list($params, $optionalParams) = partition(function($param) {
        return $param->default
            || ($param->variadic && (string) $param->var->name == 'optionalArgs');
    }, $fn->params);

    // return all params but the first and reverse them
    $revParams = array_merge(array_reverse($params), $optionalParams);

    // if we have only one requried arg, then we allow zero required args in curried implementation
    $numParams = count($params) > 1 ? count($params) - 2 : count($params) - 1;
    $stmts = array_reduce(range(0, $numParams), function($stmts, $i) use ($revParams) {
        $remainingArgsForUse = array_slice($revParams, $i + 1);
        $curParam = $revParams[$i];
        return [new Return_(new Closure([
            'params' => [clone $curParam],
            'uses' => array_map(function($param) {
                return new ClosureUse(clone $param->var);
            }, $remainingArgsForUse),
            'stmts' => $stmts,
        ]))];
    }, $fn->getStmts());

    return new Function_($fn->name, [
        'params' => array_merge(
            count($params) > 1 ? [$params[0]] : [],
            $optionalParams
        ),
        'stmts' => $stmts,
    ]);
}

function isCurryable($func) {
    if (in_array((string) $func->name, ['curry', 'autoCurry'])) {
        return false;
    }
    $numReqParams = count(toArray(filter(function($param) {
        return $param->default === null;
    }, $func->params)));
    return $numReqParams > 1 || ($numReqParams == 1 && count($func->params) > 1);
}
