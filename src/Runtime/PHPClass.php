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

use ReflectionClass;

final class PHPClass extends PspFunction
{
    public $class;

    public function __construct($class)
    {
        try {
            $this->class = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new UnexpectedValueException($e);
        }
    }

    public function execute(array $arguments)
    {
        return $this->class->newInstanceArgs($arguments);
    }

    public function getStaticMethods()
    {
        $methods = [];
        foreach ($this->class->getMethods() as $method) {
            if (!$method->isStatic() || !$method->isPublic()) {
                continue;
            }
            $name = $method->getName();
            $callback = [$this->class->getName(), $name];
            $methods[$name] = new PHPFunction($callback);
        }

        return $methods;
    }

    public function isClassOf($instance)
    {
        return is_object($instance) && $this->class->isInstance($instance);
    }
}
