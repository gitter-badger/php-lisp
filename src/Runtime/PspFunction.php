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
use PhpLisp\Psp\Form;
use PhpLisp\Psp\PspList;
use PhpLisp\Psp\Scope;

class PspFunction implements ApplicableInterface
{
    public $scope;

    public $parameters;

    public $body;

    public static function call($func, array $args)
    {
        if ($func instanceof self) {
            return $func->execute($args);
        } elseif (is_callable($func) && is_object($func)) {
            return call_user_func_array($func, $args);
        }
        throw new \InvalidArgumentException('expected callable value');
    }

    public function __construct(
        Scope $scope,
        PspList $parameters,
        Form $body
    ) {
        $this->scope = $scope;
        $this->parameters = $parameters;
        $this->body = $body;
    }

    public function apply(Scope $scope, PspList $arguments)
    {
        $args = [];
        foreach ($arguments as $arg) {
            $args[] = $arg->evaluate($scope);
        }

        return $this->execute($args);
    }

    public function __invoke()
    {
        $args = func_get_args();

        return $this->execute($args);
    }

    public function execute(
        array $arguments
    ) {
        $local = new Scope($this->scope);
        foreach ($this->parameters as $i => $name) {
            if (!array_key_exists($i, $arguments)) {
                throw new \InvalidArgumentException('too few arguments');
            }
            $local->let($name, $arguments[$i]);
        }
        $local->let('#arguments', new PspList($arguments));
        foreach ($this->body as $form) {
            $retval = $form->evaluate($local);
        }

        return $retval;
    }
}
