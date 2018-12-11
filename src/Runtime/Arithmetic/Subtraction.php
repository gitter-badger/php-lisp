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

class Subtraction extends BuiltinFunction
{
    public function execute(array $arguments)
    {
        if (1 == $c = count($arguments)) {
            return -$arguments[0];
        } elseif ($c < 1) {
            throw new \InvalidArgumentException('least 1 argument is required');
        }
        foreach ($arguments as $value) {
            if (isset($result)) {
                $result -= $value;
            } else {
                $result = $value;
            }
        }

        return $result;
    }
}
