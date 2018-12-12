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

use PhpLisp\Psp\Psp;
use PhpLisp\Psp\PspList;
use PhpLisp\Psp\Runtime\BuiltinFunction;
use PhpLisp\Psp\Runtime\PspFunction;

final class Map extends BuiltinFunction
{
    public function execute(array $arguments)
    {
        if (!$function = array_shift($arguments)) {
            throw new \InvalidArgumentException('missing function');
        } elseif (!isset($arguments[0])) {
            throw new \InvalidArgumentException('least one list is required');
        }
        $map = [];
        foreach ($arguments as &$list) {
            if ($list instanceof \IteratorAggregate) {
                $list = $list->getIterator();
            } elseif (is_array($list)) {
                $list = new \ArrayIterator($list);
            } elseif (!($list instanceof \Iterator)) {
                throw new \InvalidArgumentException('expected list');
            }
        }
        $map = [];
        while (true) {
            $values = [];
            foreach ($arguments as $it) {
                if (!$it->valid()) {
                    break 2;
                }
                $values[] = $it->current();
                $it->next();
            }
            $map[] = PspFunction::call($function, $values);
        }

        return new PspList($map);
    }
}
