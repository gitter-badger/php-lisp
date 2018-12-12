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

use PhpLisp\Psp\Scope;
use PhpLisp\Psp\Symbol;

class SymbolTest extends TestCase
{
    public function testIdentityMap()
    {
        $this->assertSame(Symbol::get('abc'), Symbol::get('abc'));
    }

    public function testNonString()
    {
        $this->expectException(\UnexpectedValueException::class);
        Symbol::get(123);
    }

    public function testInvalidSymbol()
    {
        $this->expectException(\UnexpectedValueException::class);
        Symbol::get('(abc)');
    }

    public function testEvaluate()
    {
        $scope = new Scope();
        $scope['abc'] = 123;
        $symbol = Symbol::get('abc');
        $this->assertSame(123, $symbol->evaluate($scope));
        $symbol = Symbol::get('def');
        $this->assertNull($symbol->evaluate($scope));
    }

    public function testToString()
    {
        $symbol = Symbol::get('abc');
        $this->assertSame('abc', $symbol->__toString());
    }
}
