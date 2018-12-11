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
namespace PhpLisp\Psp\Runtime\String;

use PhpLisp\Psp\Runtime\BuiltinFunction;

final class StringJoin extends BuiltinFunction
{
    public function execute(array $arguments)
    {
        list($strs, $sep) = $arguments;
        if (is_array($strs)) {
            return join($sep, $strs);
        }
        foreach ($strs as $s) {
            if (isset($result)) {
                $result .= $sep . $s;
            } else {
                $result = $s;
            }
        }

        return $result;
    }
}
