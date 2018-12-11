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

namespace PhpLisp\Psp\Runtime\PspList;

use PhpLisp\Psp\PspList;
use PhpLisp\Psp\Runtime\BuiltinFunction;

final class Cdr extends BuiltinFunction
{
    public function execute(array $arguments)
    {
        list($list) = $arguments;
        if (is_array($list)) {
            return array_slice($list, 1);
        }
        if ($list instanceof \Iterator || $list instanceof \IteratorAggregate) {
            $it = $list instanceof \Iterator ? $list : $list->getIterator();
            if (!$it->valid()) {
                return;
            }
            $result = [];
            for ($it->next(); $it->valid(); $it->next()) {
                $result[] = $it->current();
            }

            return new PspList($result);
        }
        throw new \InvalidArgumentException('expected a list');
    }
}
