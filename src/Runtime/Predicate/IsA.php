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
namespace PhpLisp\Psp\Runtime\Predicate;

use PhpLisp\Psp\Runtime\BuiltinFunction;
use PhpLisp\Psp\Runtime\PHPClass;

final class IsA extends BuiltinFunction
{
    public function execute(array $arguments)
    {
        $object = array_shift($arguments);
        if (!isset($arguments[0])) {
            throw new \InvalidArgumentException('too few arguments');
        }
        foreach ($arguments as $class) {
            if (!($class instanceof PHPClass)) {
                throw new \InvalidArgumentException('only classes are accepted');
            }
            if ($class->isClassOf($object)) {
                return true;
            }
        }

        return false;
    }
}
