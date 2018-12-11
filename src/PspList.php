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

use PhpLisp\Psp\Exceptions\InvalidApplicationException;

class PspList extends \ArrayObject implements Form
{
    public function evaluate(Scope $scope)
    {
        $function = $this->car()->evaluate($scope);
        $applicable = $function instanceof ApplicableInterface;
        if (is_callable($function) && is_object($function)) {
            $parameters = [];
            foreach ($this->cdr() as $arg) {
                $parameters[] = $arg->evaluate($scope);
            }

            try {
                return call_user_func_array($function, $parameters);
            } catch (\Exception $e) {
                throw new \Exception('Exception evaluating ' . $this->__toString(), 0, $e);
            }
        }
        if ($applicable) {
            return $function->apply($scope, $this->cdr());
        }
        throw new InvalidApplicationException($function, $this);
    }

    public function car()
    {
        return isset($this[0]) ? $this[0] : null;
    }

    public function cdr()
    {
        if (!isset($this[0])) {
            return;
        }

        return new self(array_slice($this->getArrayCopy(), 1));
    }

    public function __toString()
    {
        foreach ($this as $form) {
            if ($form instanceof Form) {
                $strs[] = $form->__toString();
            } else {
                $strs[] = '...';
            }
        }

        return '(' . join(' ', $strs) . ')';
    }
}
