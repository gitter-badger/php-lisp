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

final class Scope implements \ArrayAccess, \IteratorAggregate
{
    public $values = [];

    public $superscope;

    public function __construct(self $superscope = null)
    {
        $this->superscope = $superscope;
    }

    protected static function _symbol($symbol)
    {
        if ($symbol instanceof Symbol) {
            return $symbol->symbol;
        } elseif (is_string($symbol)) {
            return $symbol;
        }
        $type = is_object($symbol) ? get_class($symbol) : gettype($symbol);
        throw new \UnexpectedValueException("expected symbol, but $type given");
    }

    public function let($symbol, $value)
    {
        $this->values[self::_symbol($symbol)] = $value;
    }

    public function define($symbol, $value)
    {
        for ($s = $this; !is_null($s->superscope); $s = $s->superscope);
        $s->let($symbol, $value);
    }

    public function offsetGet($symbol)
    {
        $sym = self::_symbol($symbol);
        if (array_key_exists($sym, $this->values)) {
            return $this->values[$sym];
        } elseif ($this->superscope) {
            return $this->superscope[$sym];
        }
    }

    public function offsetExists($symbol)
    {
        return true;
    }

    public function offsetSet($symbol, $value)
    {
        $symbol = self::_symbol($symbol);
        $defined = false;
        for ($scope = $this; $scope; $scope = $scope->superscope) {
            if (!array_key_exists($symbol, $scope->values)) {
                continue;
            }
            $scope->values[$symbol] = $value;
            $defined = true;
            break;
        }
        if (!$defined) {
            $this->values[$symbol] = $value;
        }
    }

    public function offsetUnset($symbol)
    {
        $symbol = self::_symbol($symbol);
        unset($this->values[$symbol]);
        if ($this->superscope) {
            unset($this->superscope[$symbol]);
        }
    }

    public function getIterator()
    {
        foreach ($this->listSymbols() as $name) {
            $values[$name] = $this[$name];
        }

        return new \ArrayIterator(isset($values) ? $values : []);
    }

    public function listSymbols()
    {
        $symbols = array_keys($this->values);
        if (!$this->superscope) {
            return $symbols;
        }
        $symbols = array_merge($this->superscope->listSymbols(), $symbols);

        return array_unique($symbols);
    }
}
