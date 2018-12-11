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

final class Define implements ApplicableInterface
{
    public function apply(Scope $scope, PspList $arguments)
    {
        $name = $arguments[0];
        if ($name instanceof Symbol) {
            $retval = $arguments[1]->evaluate($scope);
        } elseif ($name instanceof PspList) {
            $params = $name->cdr();
            $body = $arguments->cdr();
            $name = $name->car();
            $retval = new PspFunction($scope, $params, $body);
        } else {
            throw new InvalidArgumentException(
                'first operand of define form must be symbol or list'
            );
        }
        $scope->define($name, $retval);

        return $retval;
    }
}
