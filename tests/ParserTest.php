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

use PhpLisp\Psp\Exceptions\ParsingException;
use PhpLisp\Psp\Literal;
use PhpLisp\Psp\Parser;
use PhpLisp\Psp\Psp;
use PhpLisp\Psp\PspList;
use PhpLisp\Psp\Quote;
use PhpLisp\Psp\Symbol;

class ParserTest extends TestCase
{
    public function assertForm($value, $offset, $expression)
    {
        $actual = Parser::parseForm($expression, $pos);
        $this->assertEquals($value, $actual);
        $this->assertEquals($offset, $pos);
    }

    public function testParse()
    {
        $expected = [
            new Literal('this is a docstring'),
            new PspList([Symbol::get('define'),
                                  new PspList([
                                      Symbol::get('add'),
                                      Symbol::get('a'),
                                      Symbol::get('b')
                                  ]),
                                  new PspList([
                                      Symbol::get('+'),
                                      Symbol::get('a'),
                                      Symbol::get('b')
                                  ])]),
            new PspList([Symbol::get('define'),
                                  new PspList([
                                      Symbol::get('sub'),
                                      Symbol::get('a'),
                                      Symbol::get('b')
                                  ]),
                                  new PspList([
                                      Symbol::get('-'),
                                      Symbol::get('a'),
                                      Symbol::get('b')
                                  ])])
        ];
        $program = '
            "this is a docstring"
            (define (add a b) (+ a b))
            (define (sub a b) (- a b))
        ';
        $this->assertEquals($expected, Parser::parse($program, true));
        $this->assertInstanceOf(Psp::class, Parser::parse($program));
        try {
            Parser::parse($code = '
                (correct form)
                (incorrect form}
                (correct form)
            ', true);
            $this->fail();
        } catch (ParsingException $e) {
            $this->assertEquals($code, $e->code);
            $this->assertEquals(63, $e->offset);
        }
    }

    public function testParseForm_list()
    {
        $expected = new PspList([
            Symbol::get('define'),
            Symbol::get('add'),
            new PspList([
                Symbol::get('lambda'),
                new PspList([
                    Symbol::get('a'),
                    Symbol::get('b')
                ]),
                new PspList([
                    Symbol::get('+'),
                    Symbol::get('a'),
                    Symbol::get('b')
                ])
            ])
        ]);
        $this->assertForm(
            $expected,
            35,
                                '(define add {lambda [a b] (+ a b)})'
        );
        try {
            Parser::parseForm('(abc d ])', $offset);
            $this->fails();
        } catch (ParsingException $e) {
            $this->assertEquals('(abc d ])', $e->code);
            $this->assertEquals(7, $e->offset);
        }
    }

    public function testParseForm_quote()
    {
        $this->assertForm(
            new Quote(Symbol::get('abc')),
            4,
                          ':abc'
        );
        $this->assertForm(
            new Quote(new PspList([
                              Symbol::get('add'),
                              new Literal(2),
                              new Literal(3)
                          ])),
                          10,
                          ':(add 2 3)'
        );
    }

    public function testParseForm_integer()
    {
        $this->assertForm(new Literal(123), 3, '123');
        $this->assertForm(new Literal(123), 4, '+123 ');
        $this->assertForm(new Literal(-123), 4, '-123');
        $this->assertForm(new Literal(0xff), 4, '0xff');
        $this->assertForm(new Literal(0xff), 5, '+0XFF');
        $this->assertForm(new Literal(-0xff), 5, '-0xFf');
        $this->assertForm(new Literal(0765), 4, '0765');
        $this->assertForm(new Literal(0765), 5, '+0765');
        $this->assertForm(new Literal(-0765), 5, '-0765');
    }

    public function testParseForm_real()
    {
        $this->assertForm(new Literal(1.234), 5, '1.234');
        $this->assertForm(new Literal(1.23), 5, '+1.23');
        $this->assertForm(new Literal(-1.23), 5, '-1.23');
        $this->assertForm(new Literal(.1234), 5, '.1234');
        $this->assertForm(new Literal(.123), 5, '+.123');
        $this->assertForm(new Literal(-.123), 5, '-.123');
        $this->assertForm(new Literal(1.2e3), 5, '1.2e3');
        $this->assertForm(new Literal(1.2e3), 6, '+1.2e3');
        $this->assertForm(new Literal(-1.2e3), 6, '-1.2e3');
    }

    public function testParseForm_string()
    {
        $this->assertForm(
            new Literal("abcd efg \"q1\"\n\t'q2'"),
                          27,
                          '"abcd efg \\"q1\\"\n\\t\\\'q2\\\'"'
        );
        $this->assertForm(
            new Literal("abcd efg 'q1'\n\t\"q2\""),
                          27,
                          "'abcd efg \\'q1\\'\\n\\t\\\"q2\\\"'"
        );
    }

    public function testParseForm_singlequotestring()
    {
        $this->assertForm(
            new Literal('foo bar'),
                          9,
                          "'foo bar'"
        );

        $this->assertForm(
            new PspList([
                              Symbol::get('if'),
                              Symbol::get('true'),
                              new Literal('Yep'),
                          ]),
                          15,
                          '(if true "Yep")'
        );

        $this->assertForm(
            new PspList([
                              Symbol::get('if'),
                              Symbol::get('true'),
                              new Literal('Yep'),
                          ]),
                          15,
                          "(if true 'Yep')"
        );
    }

    public function testParseForm_symbol()
    {
        $this->assertForm(Symbol::get('abc'), 3, 'abc');
        $this->assertForm(Symbol::get('-abcd'), 5, '-abcd ');
        $this->assertForm(Symbol::get('-'), 1, '-');
        $this->assertForm(Symbol::get('+'), 1, '+');
    }
}
