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
namespace PhpLisp\Psp\Tests;

use PhpLisp\Psp\Environment;
use PhpLisp\Psp\Psp;
use PhpLisp\Psp\Runtime\PHPFunction;

class FunctionalTest extends TestCase
{
    private $result;

    public function testFromFile()
    {
        $testFiles = glob(__DIR__ . '/fixtures/*.psp');

        foreach ($testFiles as $file) {
            $this->result = '';

            $program = Psp::load($file);
            $scope = Environment::full();
            $scope['echo'] = new PHPFunction([$this, 'displayStrings']);
            $program->execute($scope);
            $expected = file_get_contents(preg_replace('/\.psp/', '.out', $file));

            $this->assertSame(trim($expected), trim($this->result));
        }
    }

    public function displayStrings()
    {
        $args = func_get_args();
        $this->result .= join('', array_map('strval', $args));
    }
}
