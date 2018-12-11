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
namespace PhpLisp\Psp\Runtime\Predicate;

use PhpLisp\Psp\Runtime\BuiltinFunction;
use PhpLisp\Psp\Scope;

final class Type extends BuiltinFunction
{
    public static $types = ['array', 'binary', 'bool', 'buffer', 'double',
                          'float', 'int', 'integer', 'long', 'null', 'numeric',
                          'object', 'real', 'resource', 'scalar', 'string'];

    public $type;

    public static function getFunctions(Scope $superscope = null)
    {
        $scope = new Scope($superscope);
        foreach (self::$types as $type) {
            $scope["$type?"] = new self($type);
        }
        $scope['nil?'] = new self('null');

        return $scope;
    }

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function execute(array $arguments)
    {
        $function = "is_{$this->type}";
        foreach ($arguments as $value) {
            if (!$function($value)) {
                return false;
            }
        }

        return true;
    }
}
