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

use PhpLisp\Psp\Literal;
use PhpLisp\Psp\Scope;

class LiteralTest extends TestCase
{
    public static $values = ['integer' => 123, 'real' => 3.14, 'string' => 'abc'];

    public function testUnexpectedValue()
    {
        $this->expectException(\UnexpectedValueException::class);
        new Literal(new \stdClass());
    }

    public function testValue()
    {
        foreach (self::$values as $_ => $value) {
            $literal = new Literal($value);
            $this->assertSame($value, $literal->value);
        }
    }

    public function testEvaluate()
    {
        foreach (self::$values as $_ => $value) {
            $literal = new Literal($value);
            $this->assertSame($value, $literal->evaluate(new Scope()));
        }
    }

    public function testPredicate()
    {
        foreach (self::$values as $type => $value) {
            $literal = new Literal($value);
            $this->assertTrue($literal->{"is$type"}());
        }
    }
}
