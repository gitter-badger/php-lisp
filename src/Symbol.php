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

class Symbol implements Form
{
    const PATTERN = '{^
        [^ \s \d () {} \[\] : +-] [^\s () {} \[\] :]*
    |   [+-] ([^ \s \d () {} \[\] :] [^ \s () {} \[\]]*)?
    $}x';

    protected static $map = [];

    public $symbol;

    public static function get($symbol)
    {
        if (isset(self::$map[$symbol])) {
            return self::$map[$symbol];
        }

        return self::$map[$symbol] = new self($symbol);
    }

    protected function __construct($symbol)
    {
        if (!is_string($symbol)) {
            throw new \UnexpectedValueException('expected string');
        } elseif (!preg_match(self::PATTERN, $symbol)) {
            throw new \UnexpectedValueException('invalid symbol');
        }
        $this->symbol = $symbol;
    }

    public function evaluate(Scope $scope)
    {
        return $scope[$this];
    }

    public function __toString()
    {
        return $this->symbol;
    }
}
