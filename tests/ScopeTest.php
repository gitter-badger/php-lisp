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

class ScopeTest extends TestCase
{
    public function setUp()
    {
        $this->scope = new Scope();
        $this->scope['abc'] = 1;
        $this->scope['def'] = true;
        $this->scope[Symbol::get('ghi')] = null;
    }

    public function testGet()
    {
        $this->assertSame(1, $this->scope['abc']);
        $this->assertTrue($this->scope[Symbol::get('def')]);
        $this->assertNull($this->scope['ghi']);
        $this->assertNull($this->scope['x']);
    }

    public function testExists()
    {
        $this->assertTrue(isset($this->scope['abc']));
        $this->assertTrue(isset($this->scope['x']));
    }

    public function testUnset()
    {
        unset($this->scope['abc']);
        $this->assertNull($this->scope['abc']);
    }

    public function testSuperscope()
    {
        $scope = new Scope($this->scope);
        $this->assertSame($this->scope, $scope->superscope);
        $this->assertSame(1, $scope['abc']);
        $this->assertNull($scope['x']);
        $this->scope['abc'] = 2;
        $this->assertSame(2, $this->scope['abc']);
        $this->assertSame(2, $scope['abc']);
        $scope['abc'] = 3;
        $this->assertSame(3, $this->scope['abc']);
        $this->assertSame(3, $scope['abc']);
        $scope['abc'] = null;
        $this->assertNull($scope['abc']);
        $this->assertNull($this->scope['abc']);
        $scope['def'] = false;
        unset($scope['def']);
        $this->assertNull($scope['def']);
        $this->assertNull($this->scope['def']);
    }

    public function testLet()
    {
        $scope = new Scope($this->scope);
        $scope->let('abc', 'overridden');
        $this->assertSame('overridden', $scope['abc']);
    }

    public function testListSymbols()
    {
        $this->assertSame(
            [],
            array_diff(['abc', 'def', 'ghi'], $this->scope->listSymbols())
        );
        $scope = new Scope($this->scope);
        $scope->let('jkl', 123);
        $scope->let('abc', 456);
        $this->assertSame(
            [],
            array_diff(['def', 'ghi', 'jkl', 'abc'], $scope->listSymbols())
        );
    }
}
