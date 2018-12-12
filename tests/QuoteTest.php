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
use PhpLisp\Psp\PspList;
use PhpLisp\Psp\Quote;
use PhpLisp\Psp\Scope;
use PhpLisp\Psp\Symbol;

class QuoteTest extends TestCase
{
    public function testEvaluate()
    {
        $quote = new Quote(Symbol::get('abc'));
        $this->assertSame(
            Symbol::get('abc'),
                            $quote->evaluate(new Scope())
        );
    }

    public function testToString()
    {
        $quote = new Quote(Symbol::get('abc'));
        $this->assertSame(':abc', $quote->__toString());
        $quote = new Quote(new PspList([
            Symbol::get('define'),
            Symbol::get('pi'),
            new Literal(3.14)
        ]));
        $this->assertSame(':(define pi 3.14)', $quote->__toString());
    }
}
