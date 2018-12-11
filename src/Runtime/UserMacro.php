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

class UserMacro implements ApplicableInterface
{
    public $scope;

    public $body;

    public function __construct(Scope $scope, PspList $body)
    {
        $this->scope = $scope;
        $this->body = $body;
    }

    public function apply(Scope $scope, PspList $arguments)
    {
        $call = new Scope($this->scope);
        $call->let('#scope', $scope);
        $call->let('#arguments', $arguments);
        foreach ($this->body as $form) {
            $retval = $form->evaluate($call);
        }
        if (isset($retval)) {
            return $retval;
        }
    }
}
