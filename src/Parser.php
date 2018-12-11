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

final class Parser
{
    const PARENTHESES = '(){}[]';

    const QUOTE_PREFIX = ':';

    const WHITESPACES = " \t\n\r\f\v\0";

    const REAL_PATTERN = '{^
        [+-]? ((\d+ | (\d* \. \d+ | \d+ \. \d*)) e [+-]? \d+
              | \d* \. \d+ | \d+ \. \d*)
    }ix';

    const INTEGER_PATTERN = '/^([+-]?)(0x([0-9a-f]+)|0([0-7]+)|[1-9]\d*|0)/i';

    const STRING_PATTERN = '/^("([^"\\\\]|\\\\.)*"|\'([^\'\\\\]|\\\\.)*\')/';

    const STRING_ESCAPE_PATTERN = '/\\\\(([0-7]{1,3})|x([0-9A-Fa-f]{1,2})|.)/';

    const COMMENT_PATTERN = '/^;.*/';

    const SYMBOL_PATTERN = '{^
        [^ \s \d () {} \[\] : +-] [^\s () {} \[\] :]*
    |   [+-] ([^ \s \d () {} \[\] :] [^ \s () {} \[\]]*)?
    }x';

    public static function parse($program, $asArray = false)
    {
        if (!$asArray) {
            return new Psp($program);
        }
        $i = 0;
        $len = strlen($program);
        $forms = [];
        while ($i < $len) {
            if (strpos(self::WHITESPACES, $program[$i]) === false) {
                try {
                    $form = self::parseForm(substr($program, $i), $offset);
                    if ($form) {
                        $forms[] = $form;
                    }
                } catch (ParsingException $e) {
                    throw new ParsingException(
                        $program,
                        $e->offset + $i
                    );
                }
                $i += $offset;
            } else {
                ++$i;
            }
        }

        return $forms;
    }

    public static function parseForm($form, &$offset)
    {
        static $parentheses = null;
        if (is_null($parentheses)) {
            $_parentheses = self::PARENTHESES;
            $parentheses = [];
            for ($i = 0, $len = strlen($_parentheses); $i < $len; $i += 2) {
                $parentheses[$_parentheses[$i]] = $_parentheses[$i + 1];
            }
            unset($_parentheses);
        }
        if (isset($form[0], $parentheses[$form[0]])) {
            $end = $parentheses[$form[0]];
            $values = [];
            $i = 1;
            $len = strlen($form);
            while ($i < $len && $form[$i] != $end) {
                if (strpos(self::WHITESPACES, $form[$i]) !== false) {
                    ++$i;
                    continue;
                }
                try {
                    $value = self::parseForm(substr($form, $i), $_offset);
                    if ($value) {
                        $values[] = $value;
                    }
                    $i += $_offset;
                } catch (ParsingException $e) {
                    throw new ParsingException($form, $i + $e->offset);
                }
            }
            if (isset($form[$i]) && $form[$i] == $end) {
                $offset = $i + 1;

                return new PspList($values);
            }
            throw new ParsingException($form, $i);
        } elseif (isset($form[0]) && $form[0] == self::QUOTE_PREFIX) {
            $parsed = self::parseForm(substr($form, 1), $_offset);
            if (!$parsed) {
                throw new ParsingException($form, 0);
            }
            $offset = $_offset + 1;

            return new Quote($parsed);
        } elseif (preg_match(self::REAL_PATTERN, $form, $matches)) {
            $offset = strlen($matches[0]);

            return new Literal((float) $matches[0]);
        } elseif (preg_match(self::INTEGER_PATTERN, $form, $matches)) {
            $offset = strlen($matches[0]);
            $sign = $matches[1] == '-' ? -1 : 1;
            $value = !empty($matches[3]) ? hexdec($matches[3])
                : (!empty($matches[4]) ? octdec($matches[4]) : $matches[2]);

            return new Literal($sign * $value);
        } elseif (preg_match(self::STRING_PATTERN, $form, $matches)) {
            list($parsed) = $matches;
            $offset = strlen($parsed);

            return new Literal(
                preg_replace_callback(
                    self::STRING_ESCAPE_PATTERN,
                    [__CLASS__, '_unescapeString'],
                    substr($parsed, 1, -1)
                )
            );
        } elseif (preg_match(self::COMMENT_PATTERN, $form, $matches)) {
            $offset = strlen($matches[0]);

            return null;
        } elseif (preg_match(self::SYMBOL_PATTERN, $form, $matches)) {
            $offset = strlen($matches[0]);

            return Symbol::get($matches[0]);
        }
        throw new ParsingException($form, 0);
    }

    protected static function _unescapeString($matches)
    {
        static $map = ['n' => "\n", 'r' => "\r", 't' => "\t", 'v' => "\v",
            'f' => "\f"];
        if (!empty($matches[2])) {
            return chr(octdec($matches[2]));
        } elseif (!empty($matches[3])) {
            return chr(hexdec($matches[3]));
        } elseif (isset($map[$matches[1]])) {
            return $map[$matches[1]];
        } else {
            return $matches[1];
        }
    }
}
