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

class Literal implements Form
{
    public $value;

    public function __construct($value)
    {
        if (!in_array(gettype($value), ['integer', 'double', 'string'])) {
            $msg = 'it accepts only numbers or strings';
            throw new \UnexpectedValueException($msg);
        }
        $this->value = $value;
    }

    public function evaluate(Scope $scope)
    {
        return $this->value;
    }

    public function isInteger()
    {
        return is_int($this->value);
    }

    public function isReal()
    {
        return is_float($this->value);
    }

    public function isString()
    {
        return is_string($this->value);
    }

    public function __toString()
    {
        return (string)var_export($this->value, true);
    }
}
