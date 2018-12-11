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

use PhpLisp\Psp\Exceptions\InvalidApplicationException;
use PhpLisp\Psp\Literal;
use PhpLisp\Psp\Parser;
use PhpLisp\Psp\PspList;
use PhpLisp\Psp\Runtime\Define;
use PhpLisp\Psp\Scope;
use PhpLisp\Psp\Symbol;

class PspListTest extends TestCase
{
    /**
     * @var PspList
     */
    private $list;

    public function setUp()
    {
        $this->list = new PspList([
            Symbol::get('define'),
            Symbol::get('pi'),
            new Literal(3.14),
        ]);
    }

    public function testInvalidApplication()
    {
        $this->expectException(InvalidApplicationException::class);
        $this->list->evaluate(new Scope());
    }

    public function testInvalidApplication2()
    {
        $this->expectException(InvalidApplicationException::class);
        $l = Parser::parseForm('("trim" "  hello  ")', $_);
        $l->evaluate(new Scope());
    }

    public function testEvaluate()
    {
        $scope = new Scope();
        $scope['define'] = new Define();
        $this->assertSame(3.14, $this->list->evaluate($scope));
        $this->assertSame(3.14, $scope['pi']);
    }

    public function testEvaluate530()
    {
        $scope = new Scope();
        $scope['f'] = function ($a, $b) {
            return $a + $b;
        };
        $list = new PspList([
            Symbol::get('f'),
            new Literal(123),
            new Literal(456),
        ]);
        $this->assertSame(579, $list->evaluate($scope));
    }

    public function testCar()
    {
        $this->assertSame($this->list[0], $this->list->car());
    }

    public function testCdr()
    {
        $this->assertSame(
            new PspList([
                Symbol::get('pi'),
                new Literal(3.14),
            ]),
            $this->list->cdr()
        );
    }

    public function testToString()
    {
        $this->assertSame('(define pi 3.14)', $this->list->__toString());
    }
}
