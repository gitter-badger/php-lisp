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
namespace PhpLisp\Psp\Runtime;

use PhpLisp\Psp\ApplicableInterface;
use PhpLisp\Psp\PspList;
use PhpLisp\Psp\Scope;
use PhpLisp\Psp\Symbol;

final class From implements ApplicableInterface
{
    public function apply(Scope $scope, PspList $arguments)
    {
        $tmp = new Scope;
        $use = new PspUse();
        $ns = (string) $arguments->car();
        $simpleNames = iterator_to_array($arguments[1]);
        foreach ($simpleNames as $name) {
            $name = substr($name->symbol, 1, -1);
            $names[] = Symbol::get("<$ns\\$name>");
        }
        $retval = $use->apply($tmp, new PspList($names));
        foreach ($simpleNames as $i => $name) {
            $scope->let($name, $retval[$i]);
        }

        return $retval;
    }
}
