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

namespace PhpLisp\Psp\Exceptions;

use PhpLisp\Psp\PspList;

class InvalidApplicationException extends Exception
{
    public $valueToApply;

    public function __construct($valueToApply, PspList $list = null)
    {
        $this->valueToApply = $valueToApply;
        $this->list = $list;
        $type = is_object($this->valueToApply)
            ? get_class($this->valueToApply)
            : (is_null($this->valueToApply) ? 'nil'
                : gettype($this->valueToApply));
        $msg = "$type cannot be applied; see Lisphp_Applicable interface";
        if ($list) {
            $msg .= ': ' . $list->__toString();
        }
        parent::__construct($msg);
    }
}
