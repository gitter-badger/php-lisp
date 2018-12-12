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
use PhpLisp\Psp\Symbol;

final class PspUse implements ApplicableInterface
{
    public function apply(Scope $scope, PspList $arguments)
    {
        $values = [];
        foreach ($arguments as $name) {
            foreach ($this->dispatch($name) as $name => $value) {
                $scope->let($name, $value);
            }
            $values[] = $value;
        }

        return new PspList($values);
    }

    public function dispatch(Form $name)
    {
        if ($name instanceof Symbol) {
            $phpname = $name = $name->symbol;
        } else {
            $phpname = $name[0]->symbol;
            $name = $name[1]->symbol;
        }
        $phpname = str_replace('-', '_', $phpname);
        try {
            if (preg_match('|^<(.+?)>$|', $phpname, $matches)) {
                $phpname = substr($phpname, 1, -1);
                $class = new PHPClass($phpname);
                foreach ($class->getStaticMethods() as $methodName => $method) {
                    $objs["$name/$methodName"] = $method;
                }
                $objs[$name] = $class;

                return $objs;
            }
            if (preg_match('|^\+(.+?)\+$|', $phpname, $matches)) {
                $phpname = substr($phpname, 1, -1);
                $objs[$name] = constant($phpname);

                return $objs;
            }

            return [$name => new PHPFunction($phpname)];
        } catch (\UnexpectedValueException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }
}
