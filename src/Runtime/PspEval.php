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
use PhpLisp\Psp\Scope;

final class PspEval implements ApplicableInterface
{
    public function apply(Scope $scope, \PhpLisp\Psp\PspList $args)
    {
        $form = $args[0]->evaluate($scope);

        return $form->evaluate(isset($args[1]) ? $args[1]->evaluate($scope)
                                               : $scope);
    }
}
