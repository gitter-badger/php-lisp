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

namespace PhpLisp\Psp\Tests;

use ArrayObject;
use InvalidArgumentException;
use OutOfRangeException;
use PhpLisp\Psp\ApplicableInterface;
use PhpLisp\Psp\Environment;
use PhpLisp\Psp\Literal;
use PhpLisp\Psp\Parser;
use PhpLisp\Psp\PspList;
use PhpLisp\Psp\Quote;
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
use PhpLisp\Psp\Runtime\PHPClass;
use PhpLisp\Psp\Runtime\PHPFunction;
use PhpLisp\Psp\Runtime\Predicate\Eq;
use PhpLisp\Psp\Runtime\Predicate\Equal;
use PhpLisp\Psp\Runtime\Predicate\IsA;
use PhpLisp\Psp\Runtime\Predicate\NotEq;
use PhpLisp\Psp\Runtime\Predicate\NotEqual;
use PhpLisp\Psp\Runtime\Predicate\Type;
use PhpLisp\Psp\Runtime\PspArray;
use PhpLisp\Psp\Runtime\PspDo;
use PhpLisp\Psp\Runtime\PspEval;
use PhpLisp\Psp\Runtime\PspFunction;
use PhpLisp\Psp\Runtime\PspList\At;
use PhpLisp\Psp\Runtime\PspList\Car;
use PhpLisp\Psp\Runtime\PspList\Cdr;
use PhpLisp\Psp\Runtime\PspList\Count;
use PhpLisp\Psp\Runtime\PspList\ExistsAt;
use PhpLisp\Psp\Runtime\PspList\Filter;
use PhpLisp\Psp\Runtime\PspList\Fold;
use PhpLisp\Psp\Runtime\PspList\Map;
use PhpLisp\Psp\Runtime\PspList\SetAt;
use PhpLisp\Psp\Runtime\PspList\UnsetAt;
use PhpLisp\Psp\Runtime\PspUse;
use PhpLisp\Psp\Runtime\String\Concat;
use PhpLisp\Psp\Runtime\String\StringJoin;
use PhpLisp\Psp\Runtime\UserMacro;
use PhpLisp\Psp\Scope;
use PhpLisp\Psp\Symbol;
use stdClass;
use UnexpectedValueException;
use \RuntimeException;

final class SampleClass
{
    const PI = 3.14;

    public static function a()
    {
        return 'a';
    }

    public static function b()
    {
        return 'b';
    }
}

class RuntimeTest extends TestCase
{
    public static function lst($code)
    {
        $_ = 0;

        return Parser::parseForm("[$code]", $_);
    }

    public function testEval()
    {
        $_ = 0;
        $eval = new PspEval();
        $form = Parser::parseForm(':(+ 1 2 [- 4 3])', $_);
        $scope = Environment::sandbox();
        $args = new PspList([$form]);
        $this->assertSame(4, $eval->apply($scope, $args));
        $args = new PspList([$form, Symbol::get('scope')]);
        $names = new Scope();
        $names['scope'] = $scope;
        $this->assertSame(4, $eval->apply($names, $args));
    }

    public function testDefine()
    {
        $define = new Define();
        $scope = new Scope(Environment::sandbox());
        $result = $define->apply($scope, new PspList([
            Symbol::get('*pi*'),
            new Literal(pi()),
        ]));
        $this->assertSame(pi(), $result);
        $this->assertSame(pi(), $scope['*pi*']);
        $result = $define->apply($scope, new PspList([
            Symbol::get('pi2'),
            Symbol::get('*pi*'),
        ]));
        $this->assertSame(pi(), $result);
        $this->assertSame(pi(), $scope['pi2']);
        $result = $define->apply($scope, self::lst('[add a b] {+ a b}'));
        $this->assertSame($result, $scope['add']);
        $this->assertInstanceOf(PspFunction::class, $result);
        $this->assertFunction(3, $result, 1, 2);
    }

    public function testLet()
    {
        $let = new Let();
        $scope = Environment::sandbox();
        $scope['a'] = 1;
        $scope['c'] = 1;
        $retval = $let->apply(
            $scope,
            self::lst('[(a 2) (b 1)] (define c 2) (+ a b)')
        );
        $this->assertSame(2, $scope['c']);
        $this->assertSame(3, $retval);
        $this->assertSame(1, $scope['a']);
        $this->assertNull($scope['b']);
    }

    public function testQuote()
    {
        $quote = new \PhpLisp\Psp\Runtime\Quote();
        $this->assertSame(
            Symbol::get('abc'),
            $quote->apply(new Scope, new PspList(
                [Symbol::get('abc')]
            ))
        );
    }

    public function logTest($log)
    {
        $this->logged = $log;
    }

    public function testUserMacro()
    {
        $scope = Environment::sandbox();
        $scope['log'] = new PHPFunction([$this, 'logTest']);
        $body = self::lst('(log "testUserMacro") (PspList #scope #arguments)');
        $macro = new UserMacro($scope, $body);
        $this->assertSame($scope, $macro->scope);
        $this->assertSame($body, $macro->body);
        $context = new Scope;
        $args = self::lst('a (+ a b)');
        /*        $retval = $macro->apply($context, $args);
                $this->assertInstanceOf('PspList', $retval);
                $this->assertSame($context, $retval[0]);
                $this->assertSame($args, $retval[1]);*/
    }

    public function testMacro()
    {
        $macro = new Macro();
        $args = self::lst('(+ 1 2)');
        $scope = new Scope;
        $retval = $macro->apply($scope, $args);
        $this->assertInstanceOf(UserMacro::class, $retval);
        $this->assertSame($scope, $retval->scope);
        $this->assertSame($args, $retval->body);
    }

    public function testLambda()
    {
        $lambda = new Lambda();
        $scope = new Scope;
        $args = self::lst('[a b] (define x 2) (+ a b)');
        $func = $lambda->apply($scope, $args);
        $this->assertInstanceOf(PspFunction::class, $func);
        $this->assertSame($scope, $func->scope);
        $this->assertSame($args->car(), $func->parameters);
        $this->assertEquals($args->cdr(), $func->body);
    }

    public function testDo()
    {
        $do = new PspDo();
        $scope = new Scope(Environment::sandbox());
        $scope['a'] = new PspList;
        $args = self::lst('(set-at! a "first")
                           (set-at! a "second")
                           (set-at! a "third")');
        $retval = $do->apply($scope, $args);
        $this->assertSame('third', $retval);
        $this->assertEquals(
            new PspList(['first', 'second', 'third']),
            $scope['a']
        );
    }

    public function testIf()
    {
        $if = new PspIf();
        $scope = new Scope;
        $scope['define'] = new Define;
        $args = [
            Symbol::get('condition'),
            new PspList([
                Symbol::get('define'),
                Symbol::get('a'),
                new Literal(1),
            ]),
            new PspList([
                Symbol::get('define'),
                Symbol::get('b'),
                new Literal(2),
            ]),
        ];
        $scope['condition'] = true;
        $scope['a'] = $scope['b'] = 0;
        $retval = $if->apply($scope, new PspList($args));
        $this->assertSame(1, $retval);
        $this->assertSame(1, $scope['a']);
        $this->assertSame(0, $scope['b']);
        $scope['condition'] = false;
        $scope['a'] = $scope['b'] = 0;
        $retval = $if->apply($scope, new PspList($args));
        $this->assertSame(2, $retval);
        $this->assertSame(0, $scope['a']);
        $this->assertSame(2, $scope['b']);
    }

    public function applyFunction(ApplicableInterface $function)
    {
        $args = func_get_args();
        array_shift($args);
        $scope = new Scope;
        $symbol = 0;
        foreach ($args as &$value) {
            if ($value instanceof \ArrayObject || is_array($value)) {
                $value = new Quote(new PspList($value));
            } elseif (is_object($value) || is_bool($value) || is_null($value)) {
                $scope["tmp-$symbol"] = $value;
                $value = Symbol::get('tmp-'.$symbol++);
            } else {
                $value = new Literal($value);
            }
        }

        return $function->apply($scope, new PspList($args));
    }

    public function assertFunction($expected, ApplicableInterface $function)
    {
        $args = func_get_args();
        array_shift($args);
        $this->assertEquals(
            $expected,
            call_user_func_array([$this, 'applyFunction'], $args)
        );
    }

    public function testFunction()
    {
        //$this->markTestIncomplete('Somebody please debug this, I have no clue what is going on.');

        $global = new Scope(Environment::sandbox());
        $global['x'] = 1;
        $params = self::lst('a b');
        $body = self::lst('(define x 2) (+ a b)');
        $func = new PspFunction($global, $params, $body);
        $this->assertSame($global, $func->scope);
        $this->assertSame($params, $func->parameters);
        $this->assertSame($body, $func->body);
        $this->assertFunction(3, $func, 1, 2);
        $this->assertEquals(1, $global['x']);
        try {
            $this->applyFunction($func, 1);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            # pass.
        }
        $body = self::lst('#arguments');
        $func = new PspFunction($global, new PspList, $body);
        $this->assertFunction(new PspList, $func);
        $this->assertFunction(new PspList(range(1, 3)), $func, 1, 2, 3);
    }

    public function testGenericCall()
    {
        $val = PspFunction::call(
            new Addition(),
            [1, 2]
        );
        $this->assertSame(3, $val);
        try {
            PspFunction::call('trim', ['a']);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            # pass
        }
        try {
            PspFunction::call(1, []);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            # pass
        }
    }

    public function testGenericCall530()
    {
        $f = function ($a, $b) {
            return $a + $b;
        };;
        $val = PspFunction::call($f, [1, 2]);
        $this->assertSame(3, $val);
    }

    public function testApply()
    {
        $apply = new Apply();
        $add = new Addition();
        $this->assertFunction(9, $apply, $add, new PspList([2, 3, 4]));
    }

    public function testAdd()
    {
        $add = new Addition();
        $this->assertFunction(5, $add, 5);
        $this->assertFunction(10, $add, 5, 5);
        $this->assertFunction(6, $add, 1, 2, 3);
    }

    public function testSubtract()
    {
        $sub = new Subtraction();
        $this->assertFunction(-5, $sub, 5);
        $this->assertFunction(2, $sub, 5, 3);
        $this->assertFunction(1, $sub, 5, 3, 1);
    }

    public function testMultiply()
    {
        $mul = new Multiplication();
        $this->assertFunction(1, $mul);
        $this->assertFunction(5, $mul, 5);
        $this->assertFunction(25, $mul, 5, 5);
        $this->assertFunction(50, $mul, 5, 5, 2);
    }

    public function testDivide()
    {
        $div = new Division();
        $this->assertFunction(5, $div, 25, 5);
        $this->assertFunction(5, $div, 50, 2, 5);
    }

    public function testMod()
    {
        $mod = new Modulus();
        $this->assertFunction(0, $mod, 25, 5);
        $this->assertFunction(1, $mod, 25, 4);
    }

    public function testNot()
    {
        $not = new Not();
        $this->assertFunction(false, $not, true);
        $this->assertFunction(true, $not, false);
        $this->assertFunction(false, $not, 1);
        $this->assertFunction(false, $not, 2);
        $this->assertFunction(true, $not, 0);
        $this->assertFunction(false, $not, 'abc');
        $this->assertFunction(true, $not, '');
    }

    public function testAnd()
    {
        //$this->markTestIncomplete('Somebody please debug this, I have no clue what is going on.');

        $and = new PspAnd();
        $this->assertFunction(false, $and, false);
        $this->assertFunction(true, $and, true);
        $this->assertFunction(false, $and, false, false);
        $this->assertFunction(false, $and, false, true);
        $this->assertFunction(false, $and, true, false);
        $this->assertFunction(true, $and, true, true);
        $this->assertFunction(false, $and, false, false, false);
        $this->assertFunction(false, $and, false, true, false);
        $this->assertFunction(false, $and, false, false, true);
        $this->assertFunction(false, $and, false, true, true);
        $this->assertFunction(true, $and, true, true, true);
        $this->assertFunction('', $and, 'a', '');
        $this->assertFunction(null, $and, 'a', null);
        $this->assertFunction('b', $and, 'a', 'b');
        $this->assertFunction('', $and, 'a', 'b', '');
        $this->assertFunction(null, $and, 'a', 'b', null);
        $this->assertFunction('c', $and, 'a', 'b', 'c');
        $env = Environment::sandbox();
        $scope = new Scope($env);
        $scope['a'] = 1;
        $retval = $and->apply($scope, self::lst('(define a 2)
                                                 (define b 0)
                                                 (define a 3)'));
        $this->assertSame(0, $retval);
        $this->assertSame(1, $scope['a']);
    }

    public function testOr()
    {
        //$this->markTestIncomplete('Somebody please debug this, I have no clue what is going on.');

        $or = new PspOr();
        $this->assertFunction(false, $or, false);
        $this->assertFunction(true, $or, true);
        $this->assertFunction(false, $or, false, false);
        $this->assertFunction(true, $or, true, false);
        $this->assertFunction(true, $or, false, true);
        $this->assertFunction(true, $or, true, true);
        $this->assertFunction(false, $or, false, false, false);
        $this->assertFunction(true, $or, false, false, true);
        $this->assertFunction(true, $or, false, true, false);
        $this->assertFunction(true, $or, true, false, false);
        $this->assertFunction(true, $or, true, true, false);
        $this->assertFunction(true, $or, false, true, true);
        $this->assertFunction(true, $or, true, false, true);
        $this->assertFunction(true, $or, true, true, true);
        $this->assertFunction('a', $or, 'a', '');
        $this->assertFunction('', $or, null, '');
        $this->assertFunction('b', $or, '', 'b');
        $this->assertFunction('a', $or, 'a', 'b', 'c');
        $this->assertFunction('c', $or, false, null, 'c');
        $env = Environment::sandbox();
        $scope = new Scope($env);
        $scope['a'] = 1;
        $retval = $or->apply($scope, self::lst('(define b 0)
                                                (define a 2)
                                                (define a 3)'));
        $this->assertSame(2, $retval);
        $this->assertSame(1, $scope['a']);
        $this->assertSame(0, $scope['b']);
    }

    public function testEq()
    {
        $eq = new Eq();
        $this->assertFunction(false, $eq, new \stdClass, new \stdClass);
        $o = new \stdClass;
        $this->assertFunction(true, $eq, $o, $o);
        $this->assertFunction(true, $eq, 3, 3);
        $this->assertFunction(false, $eq, 3, 3.0);
        $this->assertFunction(true, $eq, 3.0, 3.0);
        $this->assertFunction(true, $eq, 'foo', 'foo');
        $this->assertFunction(false, $eq, 'foo', 'bar');
        $this->assertFunction(true, $eq, 3.0, 3.0, 3.0);
        $this->assertFunction(false, $eq, 3, 3.0, 3.0);
        $this->assertFunction(true, $eq, 'foo', 'foo', 'foo');
        $this->assertFunction(false, $eq, 'foo', 'foo', 'bar');
        try {
            $this->applyFunction($eq);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($eq, 1);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            # pass.
        }
    }

    public function testEqual()
    {
        $eq = new Equal();
        $this->assertFunction(true, $eq, new \stdClass, new stdClass);
        $this->assertFunction(true, $eq, 3, 3);
        $this->assertFunction(true, $eq, 3, 3.0);
        $this->assertFunction(true, $eq, 3.0, 3.0);
        $this->assertFunction(true, $eq, 'foo', 'foo');
        $this->assertFunction(false, $eq, 'foo', 'bar');
        $this->assertFunction(true, $eq, 3.0, 3.0, 3.0);
        $this->assertFunction(true, $eq, 3, 3.0, 3.0);
        $this->assertFunction(true, $eq, 'foo', 'foo', 'foo');
        $this->assertFunction(false, $eq, 'foo', 'foo', 'bar');
        try {
            $this->applyFunction($eq);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($eq, 1);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    public function testNotEq()
    {
        $ne = new NotEq();
        $this->assertFunction(true, $ne, new stdClass, new stdClass);
        $o = new stdClass;
        $this->assertFunction(false, $ne, $o, $o);
        $this->assertFunction(false, $ne, 3, 3);
        $this->assertFunction(true, $ne, 3, 3.0);
        $this->assertFunction(false, $ne, 3.0, 3.0);
        $this->assertFunction(false, $ne, 'foo', 'foo');
        $this->assertFunction(true, $ne, 'foo', 'bar');
        $this->assertFunction(false, $ne, 3.0, 3.0, 3.0);
        $this->assertFunction(true, $ne, 3, 3.0, 3.0);
        $this->assertFunction(false, $ne, 'foo', 'foo', 'foo');
        $this->assertFunction(true, $ne, 'foo', 'foo', 'bar');
        try {
            $this->applyFunction($ne);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($ne, 1);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    public function testNotEqual()
    {
        $ne = new NotEqual();
        $this->assertFunction(false, $ne, new stdClass, new stdClass);
        $o = new stdClass;
        $this->assertFunction(false, $ne, $o, $o);
        $this->assertFunction(false, $ne, 3, 3);
        $this->assertFunction(false, $ne, 3, 3.0);
        $this->assertFunction(false, $ne, 3.0, 3.0);
        $this->assertFunction(false, $ne, 'foo', 'foo');
        $this->assertFunction(true, $ne, 'foo', 'bar');
        $this->assertFunction(false, $ne, 3.0, 3.0, 3.0);
        $this->assertFunction(false, $ne, 3, 3.0, 3.0);
        $this->assertFunction(false, $ne, 'foo', 'foo', 'foo');
        $this->assertFunction(true, $ne, 'foo', 'foo', 'bar');
        try {
            $this->applyFunction($ne);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($ne, 1);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    public function testTypePredicate()
    {
        $int = new Type('int');
        $this->assertSame('int', $int->type);
        $this->assertFunction(true, $int, 123);
        $this->assertFunction(false, $int, 3.14);
        $this->assertFunction(false, $int, 'abc');
        $string = new Type('string');
        $this->assertSame('string', $string->type);
        $this->assertFunction(true, $string, 'hello');
        $this->assertFunction(false, $string, 123);
        $env = Type::getFunctions();
        foreach (Type::$types as $type) {
            $this->assertInstanceOf(Type::class, $env["$type?"]);
            $this->assertSame($type, $env["$type?"]->type);
        }
        $this->assertFunction(true, $env['nil?'], null);
        $this->assertFunction(false, $env['nil?'], 123);
    }

    public function testIsA()
    {
        $isa = new IsA();
        $this->assertFunction(
            true,
            $isa,
            new stdClass,
            new PHPClass('stdClass')
        );
        $this->assertFunction(
            false,
            $isa,
            new stdClass,
            new PHPClass('ArrayObject')
        );
        $this->assertFunction(
            false,
            $isa,
            1,
            new PHPClass('stdClass')
        );
        $this->assertFunction(
            true,
            $isa,
            new stdClass,
            new PHPClass('ArrayObject'),
            new PHPClass('stdClass')
        );
        $this->assertFunction(
            false,
            $isa,
            new stdClass,
            new PHPClass('ArrayObject'),
            new PHPClass('ArrayIterator')
        );
        try {
            $this->applyFunction($isa);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($isa, 1);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    public function testList()
    {
        $list = new \PhpLisp\Psp\Runtime\PspList();
        $this->assertFunction(new PspList, $list);
        $this->assertFunction(
            new PspList([1, 2, 3, 4]),
            $list,
            1,
            2,
            3,
            4
        );
    }

    public function testCar()
    {
        $car = new Car();
        $this->assertFunction(1, $car, [1, 2, 3]);
        try {
            $this->applyFunction($car, new PspList);
            $this->fails();
        } catch (UnexpectedValueException $e) {
            # pass.
        }
    }

    public function testCdr()
    {
        $cdr = new Cdr();
        $this->assertFunction(
            new PspList([2, 3]),
            $cdr,
            [1, 2, 3]
        );
        $this->assertFunction(null, $cdr, []);
        $this->assertFunction(new PspList, $cdr, [1]);
    }

    public function testAt()
    {
        $at = new At();
        $this->assertFunction(1, $at, new PspList([1, 2, 3]), 0);
        $this->assertFunction(3, $at, new PspList([1, 2, 3]), 2);
        try {
            $this->applyFunction($at, new PspList([1, 2, 3]), 3);
            $this->fail();
        } catch (\OutOfRangeException $e) {
            # pass.
        }
    }

    public function testSetAt()
    {
        $setAt = new SetAt();
        $array = new ArrayObject(['a', 'b']);
        $this->assertFunction('c', $setAt, $array, 'c');
        $this->assertEquals(new ArrayObject(['a', 'b', 'c']), $array);
        $this->assertFunction('C', $setAt, $array, 2, 'C');
        $this->assertEquals(new ArrayObject(['a', 'b', 'C']), $array);
        $this->assertFunction('d', $setAt, $array, 3, 'd');
        $this->assertEquals(new ArrayObject(['a', 'b', 'C', 'd']), $array);
    }

    public function testUnsetAt()
    {
        $unsetAt = new UnsetAt();
        $array = new ArrayObject(['a', 'b']);
        $this->assertFunction('b', $unsetAt, $array, 1);
        $this->assertEquals(new ArrayObject(['a']), $array);
        $array = new ArrayObject(['a', 'b']);
        $this->assertFunction('a', $unsetAt, $array, 0);
        $this->assertEquals(new ArrayObject([1 => 'b']), $array);
        try {
            $this->applyFunction($unsetAt, ['a', 'b'], 3);
            $this->fail();
        } catch (OutOfRangeException $e) {
            # pass.
        }
    }

    public function testExistsAt()
    {
        $existsAt = new ExistsAt();
        $this->assertFunction(true, $existsAt, [1, 2, 3], 0);
        $this->assertFunction(false, $existsAt, [1, 2, 3], 5);
    }

    public function testCount()
    {
        $count = new Count();
        $this->assertFunction(0, $count, []);
        $this->assertFunction(3, $count, [1, 2, 3]);
        $this->assertFunction(0, $count, new PspList);
        $this->assertFunction(3, $count, new PspList([1, 2, 3]));
        $this->assertFunction(0, $count, '');
        $this->assertFunction(3, $count, 'abc');
    }

    public function testArray()
    {
        $array = new PspArray();
        $this->assertFunction([], $array);
        $this->assertFunction([1, 2, 3], $array, 1, 2, 3);
    }

    public function testDict()
    {
        $dict = new Dict();
        $scope = new Scope;
        $scope['a'] = 'key';
        $retval = $dict->apply($scope, self::lst('(a 1) ("key2" 2) (3) 4'));
        $this->assertSame(['key' => 1, 'key2' => 2, 3, 4], $retval);
    }

    public function testMap()
    {
        $map = new Map();
        $func = new PspFunction(
            Environment::sandbox(),
            self::lst('a'),
            self::lst('(+ a 1)')
        );
        $this->assertFunction(new PspList(), $map, $func, []);
        $this->assertFunction(
            new PspList([2, 3]),
            $map,
            $func,
            [1, 2]
        );
        $func = new PspFunction(
            new Scope,
            self::lst(''),
            self::lst('#arguments')
        );
        $this->assertFunction(
            new PspList([
                new PspList([1, 4]),
                new PspList([2, 5]),
                new PspList([3, 6]),
            ]),
            $map,
            $func,
            [1, 2, 3],
            [4, 5, 6]
        );
        try {
            $this->applyFunction($map);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($map, $func);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($map, 1, [1]);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    public function testFilter()
    {
        $filter = new Filter();
        $this->assertFunction(
            new PspList([1, 3, 5]),
            $filter,
            new Type('int'),
            [1, '2', 3, [4], 5]
        );
    }

    public function testFoldl()
    {
        $fold = new Fold();
        $agg = new Subtraction;
        $this->assertFunction(3, $fold, $agg, [25, 9, 5, 7, 1]);
        $this->assertFunction(3, $fold, $agg, [9, 5, 7, 1], 25);
        $this->assertFunction(25, $fold, $agg, [], 25);
        try {
            $this->applyFunction($fold, $agg, []);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    public function testConcat()
    {
        $concat = new Concat();
        $this->assertFunction('ab', $concat, 'a', 'b');
        $this->assertFunction('hello world!', $concat, 'hello', ' world', '!');
        try {
            $this->applyFunction($concat);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    public function testStringJoin()
    {
        $join = new StringJoin();
        $this->assertFunction(
            'one two three',
            $join,
            ['one', 'two', 'three'],
            ' '
        );
    }

    public function methodTest($a)
    {
        return [$this, $a];
    }

    public function testPHPFunction()
    {
        $substr = new PHPFunction('substr');
        $this->assertFunction('world', $substr, 'hello world', 6);
        $method = new PHPFunction([$this, 'methodTest']);
        $this->assertFunction([$this, 123], $method, 123);
        try {
            new PHPFunction('undefined_function_name');
            $this->fail();
        } catch (UnexpectedValueException $e) {
            # pass
        }
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testPHPClass()
    {
        $class = new PHPClass('ArrayObject');
        $obj = $this->applyFunction($class, [1, 2, 3]);
        $this->assertInstanceOf('ArrayObject', $obj);
        $this->assertSame([1, 2, 3], $obj->getArrayCopy());

        //$this->expectExceptionMessage('Wrong parameters for UnexpectedValueException([string $message [, long $code [, Throwable $previous = NULL]]])');
        new PHPClass('UndefinedClassName');

        $class = new PHPClass('SampleClass');

        $methods = $class->getStaticMethods();
        $this->assertSame(2, count($methods));
        $this->assertInstanceOf('PHPFunction', $methods['a']);
        $this->assertSame(
            ['SampleClass', 'a'],
            $methods['a']->callback
        );
        $this->assertInstanceOf('PHPFunction', $methods['b']);
        $this->assertSame(
            ['SampleClass', 'b'],
            $methods['b']->callback
        );
        $this->assertTrue($class->isClassOf(new SampleClass));
        $this->assertFalse($class->isClassOf(new stdClass));
        $this->assertFalse($class->isClassOf(213));
    }

    public function testGetAttribute()
    {
        $attr = new GetAttribute();
        $object = (object)['abc' => 'value'];
        $object->ptr = $object;
        $object->lst = new PspList;
        $scope = new Scope;
        $scope->let('object', $object);
        $val = $attr->apply(
            $scope,
            self::lst('object abc')
        );
        $this->assertSame($object->abc, $val);
        $val = $attr->apply($scope, self::lst('object ptr ptr abc'));
        $this->assertSame($object->ptr->ptr->abc, $val);
        $val = $attr->apply($scope, self::lst('object lst car'));
        $this->assertInstanceOf(PHPFunction::class, $val);
        $this->assertSame($object->lst, $val->callback[0]);
        $this->assertSame('car', $val->callback[1]);
        try {
            $attr->apply($scope, self::lst('object lst a'));
            $this->fail();
        } catch (RuntimeException $e) {
            # pass
        }
    }

  /*  public function testUse()
    {
        $use = new PspUse();
        $env = Environment::sandbox();
        $scope = new Scope($env);
        $values = $use->apply($scope, self::lst('array_merge
                                                 array-slice
                                                 [implode array->string]
                                                 <\ArrayObject>
                                                 <PhpLisp\\Psp\\Symbol>
                                                 <PhpLisp\Psp\Tests\fixtures\Foo \\Bar>
                                                 [<Scope> scope]
                                                 +PHP_VERSION+
                                                 +PHP_OS+'));
        $this->assertInstanceOf('PHPFunction', $values[0]);
        $this->assertSame('array_merge', $values[0]->callback);
        $this->assertSame($values[0], $scope['array_merge']);
        $this->assertNull($env['array_merge']);
        $this->assertInstanceOf('PHPFunction', $values[1]);
        $this->assertSame('array_slice', $values[1]->callback);
        $this->assertSame($values[1], $scope['array-slice']);
        $this->assertNull($env['array-slice']);
        $this->assertInstanceOf('PHPFunction', $values[2]);
        $this->assertSame('implode', $values[2]->callback);
        $this->assertSame($values[2], $scope['array->string']);
        $this->assertNull($env['implode']);
        $this->assertInstanceOf('PHPClass', $values[3]);
        $this->assertSame('ArrayObject', $values[3]->class->getName());
        $this->assertSame($values[3], $scope['<ArrayObject>']);
        $this->assertNull($env['<ArrayObject>']);
        $this->assertInstanceOf('PHPClass', $values[4]);
        $this->assertSame('Symbol', $values[4]->class->getName());
        $this->assertSame($values[4], $scope['<Symbol>']);
        $this->assertNull($env['<Symbol>']);
        $this->assertInstanceOf('PHPClass', $values[5]);
        $this->assertSame('Foo\\Bar', $values[5]->class->getName());
        $this->assertSame($values[5], $scope['<Foo\\Bar>']);
        $this->assertInstanceOf(
            'PHPFunction',
            $scope['<Foo\\Bar>/doSomething']
        );
        $this->assertSame(
            ['Foo\\Bar', 'doSomething'],
            $scope['<Foo\\Bar>/doSomething']->callback
        );
        $this->assertNull($env['<Foo\\Bar>']);
        $this->assertInstanceOf('PHPClass', $values[6]);
        $this->assertSame('Scope', $values[6]->class->getName());
        $this->assertSame($values[6], $scope['scope']);
        $this->assertNull($env['scope']);
        $this->assertSame(PHP_VERSION, $values[7]);
        $this->assertSame(PHP_VERSION, $scope['+PHP_VERSION+']);
        $this->assertNull($env['+PHP_VERSION+']);
        $this->assertSame(PHP_OS, $values[8]);
        $this->assertSame(PHP_OS, $scope['+PHP_OS+']);
        $this->assertNull($env['+PHP_OS+']);
        try {
            $use->apply($scope, self::lst('undefined-function-name'));
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass
        }
        try {
            $use->apply($scope, self::lst('<UndefinedClassName>'));
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass
        }
    }*/

    public function testFrom()
    {
        $from = new From();
        $env = Environment::sandbox();
        $scope = new Scope($env);
        $values = $from->apply($scope, self::lst('PhpLisp\Psp\Tests\fixtures\Foo (<Bar> <Baz>)'));
        $this->assertSame('PhpLisp\\Psp\\Tests\\fixtures\\Foo\\Bar', $values[0]->class->getName());
        $this->assertSame('PhpLisp\\Psp\\Tests\\fixtures\\Foo\\Bar', $scope['<Bar>']->class->getName());
        $this->assertSame('PhpLisp\\Psp\\Tests\\fixtures\\Foo\\Baz', $values[1]->class->getName());
        $this->assertSame('PhpLisp\\Psp\\Tests\\fixtures\\Foo\\Baz', $scope['<Baz>']->class->getName());
    }
}
