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

use PhpLisp\Psp\Exceptions\ParsingException;

final class Psp implements \IteratorAggregate, \ArrayAccess, \Countable
{
    public $forms;

    public static function load($file)
    {
        if ($fp = fopen($file, 'r')) {
            for ($code = ''; !feof($fp); $code .= fread($fp, 8192)) {
                ;
            }
            fclose($fp);
            try {
                $program = new self($code);
            } catch (ParsingException $e) {
                throw new ParsingException($e->getCode(), $e->offset, $file);
            }

            return $program;
        }
    }

    public function __construct($program)
    {
        $this->forms = Parser::parse($program, true);
    }

    public function execute(Scope $scope)
    {
        $value = null;

        foreach ($this->forms as $form) {
            $value = $form->evaluate($scope);
        }

        return $value;
    }

    public function offsetGet($offset)
    {
        return $this->forms[$offset];
    }

    public function offsetExists($offset)
    {
        return isset($this->forms[$offset]);
    }

    public function offsetSet($_, $__)
    {
        throw new \BadMethodCallException('Lisphp_Program object is immutable');
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Lisphp_Program object is immutable');
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->forms);
    }

    public function count()
    {
        return count($this->forms);
    }
}
