<?php declare(strict_types=1);
/**
 * This file is part of the php-lisp/php-lisp.
 *
 * @Link     https://github.com/php-lisp/php-lisp
 * @Document https://github.com/php-lisp/php-lisp/blob/master/README.md
 * @Contact  itwujunze@gmail.com
 * @License  https://github.com/php-lisp/php-lisp/blob/master/LICENSE
 *
 * (c) Panda <itwujunze@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpLisp\Psp;

use PhpLisp\Psp\Runtime\Apply;
use PhpLisp\Psp\Runtime\Arithmetic\Addition;
use PhpLisp\Psp\Runtime\Arithmetic\Division;
use PhpLisp\Psp\Runtime\Arithmetic\Modulus;
use PhpLisp\Psp\Runtime\Arithmetic\Multiplication;
use PhpLisp\Psp\Runtime\Arithmetic\Subtraction;
use PhpLisp\Psp\Runtime\Define;
use PhpLisp\Psp\Runtime\Dict;
use PhpLisp\Psp\Runtime\From;
use PhpLisp\Psp\Runtime\Lambda;
use PhpLisp\Psp\Runtime\Let;
use PhpLisp\Psp\Runtime\Logical\Not;
use PhpLisp\Psp\Runtime\Logical\PspAnd;
use PhpLisp\Psp\Runtime\Logical\PspIf;
use PhpLisp\Psp\Runtime\Logical\PspOr;
use PhpLisp\Psp\Runtime\Macro;
use PhpLisp\Psp\Runtime\Object\GetAttribute;
use PhpLisp\Psp\Runtime\PHPFunction;
use PhpLisp\Psp\Runtime\Predicate\Eq;
use PhpLisp\Psp\Runtime\Predicate\Equal;
use PhpLisp\Psp\Runtime\Predicate\GreaterEqual;
use PhpLisp\Psp\Runtime\Predicate\GreaterThan;
use PhpLisp\Psp\Runtime\Predicate\IsA;
use PhpLisp\Psp\Runtime\Predicate\LessEqual;
use PhpLisp\Psp\Runtime\Predicate\LessThan;
use PhpLisp\Psp\Runtime\Predicate\NotEq;
use PhpLisp\Psp\Runtime\Predicate\NotEqual;
use PhpLisp\Psp\Runtime\Predicate\Type;
use PhpLisp\Psp\Runtime\PspArray;
use PhpLisp\Psp\Runtime\PspDo;
use PhpLisp\Psp\Runtime\PspEval;
use PhpLisp\Psp\Runtime\PspList\At;
use PhpLisp\Psp\Runtime\PspList\Car;
use PhpLisp\Psp\Runtime\PspList\Cdr;
use PhpLisp\Psp\Runtime\PspList\Cond;
use PhpLisp\Psp\Runtime\PspList\Count;
use PhpLisp\Psp\Runtime\PspList\ExistsAt;
use PhpLisp\Psp\Runtime\PspList\Filter;
use PhpLisp\Psp\Runtime\PspList\Fold;
use PhpLisp\Psp\Runtime\PspList\Map;
use PhpLisp\Psp\Runtime\PspList\SetAt;
use PhpLisp\Psp\Runtime\PspList\UnsetAt;
use PhpLisp\Psp\Runtime\PspUse;
use PhpLisp\Psp\Runtime\Setf;
use PhpLisp\Psp\Runtime\String\Concat;
use PhpLisp\Psp\Runtime\String\StringJoin;

final class Environment
{
    public static function sandbox()
    {
        $scope = new Scope();
        $scope['nil'] = null;
        $scope['true'] = $scope['#t'] = $scope['else'] = true;
        $scope['false'] = $scope['#f'] = false;
        $scope['eval'] = new PspEval();
        $scope['quote'] = new \PhpLisp\Psp\Runtime\Quote();
        $scope['symbol'] = new PHPFunction(function () {
            Symbol::get('');
        });
        $scope['define'] = new Define();
        $scope['setf!'] = new Setf();
        $scope['let'] = new Let();
        $scope['macro'] = new Macro();
        $scope['lambda'] = new Lambda();
        $scope['apply'] = new Apply();
        $scope['do'] = new PspDo();
        $scope['dict'] = new Dict();
        $scope['array'] = new PspArray();
        $scope['list'] = new PspList();
        $scope['car'] = new Car();
        $scope['cdr'] = new Cdr();
        $scope['at'] = new At();
        $scope['set-at!'] = new SetAt();
        $scope['unset-at!'] = new UnsetAt();
        $scope['exists-at?'] = new ExistsAt();
        $scope['count'] = new Count();
        $scope['map'] = new Map();
        $scope['filter'] = new Filter();
        $scope['fold'] = new Fold();
        $scope['cond'] = new Cond();
        $scope['=='] = $scope['eq'] = $scope['eq?']
            = new Eq();
        $scope['='] = $scope['equal'] = $scope['equal?']
            = new Equal();
        $scope['!=='] = $scope['/=='] = $scope['not-eq'] = $scope['not-eq?']
            = new NotEq();
        $scope['!='] = $scope['/='] = $scope['not-equal'] = $scope['not-equal?']
            = new NotEqual();
        $scope['<'] = new LessThan();
        $scope['>'] = new GreaterThan();
        $scope['<='] = new LessEqual();
        $scope['>='] = new GreaterEqual();
        foreach (Type::getFunctions() as $n => $f) {
            $scope[$n] = $f;
        }
        $scope['isa?'] = $scope['is-a?'] = new IsA();
        $scope['+'] = new Addition();
        $scope['-'] = new Subtraction();
        $scope['*'] = new Multiplication();
        $scope['/'] = new Division();
        $scope['%'] = $scope['mod'] = new Modulus();
        $scope['string'] = new PHPFunction('strval');
        $scope['.'] = $scope['concat'] = new Concat();
        $scope['string-join'] = new StringJoin();
        $scope['substring'] = new PHPFunction('substr');
        $scope['string-upcase'] = new PHPFunction('strtoupper');
        $scope['string-downcase'] = new PHPFunction('strtolower');
        $scope['not'] = new Not();
        $scope['and'] = new PspAnd();
        $scope['or'] = new PspOr();
        $scope['if'] = new PspIf();
        $scope['->'] = new GetAttribute();

        return $scope;
    }

    public static function full()
    {
        $scope = new Scope(self::sandbox());
        $scope->let('use', new PspUse());
        $scope->let('from', new From());
        $scope->let('*env*', $_ENV);
        $scope->let('*server*', $_SERVER);

        return $scope;
    }

    public static function webapp()
    {
        $scope = new Scope(self::sandbox());
        $scope->let('*get*', $_GET);
        $scope->let('*post*', $_POST);
        $scope->let('*request*', $_REQUEST);
        $scope->let('*files*', $_FILES);
        $scope->let('*cookie*', $_COOKIE);
        $scope->let('*session*', $_SESSION);
    }
}
