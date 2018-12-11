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
namespace PhpLisp\Psp\Runtime\Arithmetic;

use PhpLisp\Psp\Runtime\BuiltinFunction;

class Modulus extends BuiltinFunction
{
    public function execute(array $arguments)
    {
        if (isset($arguments[1])) {
            return $arguments[0] % $arguments[1];
        }
        throw new \InvalidArgumentException('2 arguments are required');
    }
}
