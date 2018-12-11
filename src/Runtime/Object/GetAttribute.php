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
namespace PhpLisp\Psp\Runtime\Object;

use PhpLisp\Psp\ApplicableInterface;
use PhpLisp\Psp\PspList;
use PhpLisp\Psp\Runtime\PHPFunction;
use PhpLisp\Psp\Scope;

final class GetAttribute implements ApplicableInterface
{
    public function apply(Scope $scope, PspList $arguments)
    {
        if (count($arguments) < 2) {
            throw new \InvalidArgumentException('expected least two arguments');
        }
        $object = $first = $arguments->car()->evaluate($scope);
        $names = $arguments->cdr();
        $chain = '';
        foreach ($names as $name) {
            $name = (string) $name;
            $chain .= "->$name";
            if (isset($object->$name)) {
                $object = $object->$name;
            } elseif (method_exists($object, $name) || method_exists($object, '__call')) {
                $object = new PHPFunction([$object, $name]);
            } else {
                $o = (is_object($first) ? get_class($first) : gettype($first))
                   . $chain;
                throw new \RuntimeException("there is no name '$name' for $o");
            }
        }

        return $object;
    }
}
