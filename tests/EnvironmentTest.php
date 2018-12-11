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
use PhpLisp\Psp\PspList;
use PhpLisp\Psp\Runtime\Apply;
use PhpLisp\Psp\Runtime\Arithmetic\Addition;
use PhpLisp\Psp\Runtime\Arithmetic\Division;
use PhpLisp\Psp\Runtime\Arithmetic\Modulus;
use PhpLisp\Psp\Runtime\Arithmetic\Multiplication;
use PhpLisp\Psp\Runtime\Arithmetic\Subtraction;
use PhpLisp\Psp\Runtime\Define;
use PhpLisp\Psp\Runtime\Dict;
use PhpLisp\Psp\Runtime\From;
use PhpLisp\Psp\Runtime\Lambda;
use PhpLisp\Psp\Runtime\Let;
use PhpLisp\Psp\Runtime\Logical\Not;
use PhpLisp\Psp\Runtime\Logical\PspAnd;
use PhpLisp\Psp\Runtime\Logical\PspIf;
use PhpLisp\Psp\Runtime\Logical\PspOr;
use PhpLisp\Psp\Runtime\Macro;
use PhpLisp\Psp\Runtime\Object\GetAttribute;
use PhpLisp\Psp\Runtime\PHPFunction;
use PhpLisp\Psp\Runtime\Predicate\Eq;
use PhpLisp\Psp\Runtime\Predicate\Equal;
use PhpLisp\Psp\Runtime\Predicate\IsA;
use PhpLisp\Psp\Runtime\Predicate\NotEq;
use PhpLisp\Psp\Runtime\Predicate\NotEqual;
use PhpLisp\Psp\Runtime\Predicate\Type;
use PhpLisp\Psp\Runtime\PspArray;
use PhpLisp\Psp\Runtime\PspDo;
use PhpLisp\Psp\Runtime\PspEval;
use PhpLisp\Psp\Runtime\PspList\At;
use PhpLisp\Psp\Runtime\PspList\Car;
use PhpLisp\Psp\Runtime\PspList\Cdr;
use PhpLisp\Psp\Runtime\PspList\Cond;
use PhpLisp\Psp\Runtime\PspList\Count;
use PhpLisp\Psp\Runtime\PspList\ExistsAt;
use PhpLisp\Psp\Runtime\PspList\Filter;
use PhpLisp\Psp\Runtime\PspList\Fold;
use PhpLisp\Psp\Runtime\PspList\Map;
use PhpLisp\Psp\Runtime\PspList\SetAt;
use PhpLisp\Psp\Runtime\PspList\UnsetAt;
use PhpLisp\Psp\Runtime\PspUse;
use PhpLisp\Psp\Runtime\Quote;
use PhpLisp\Psp\Runtime\String\Concat;
use PhpLisp\Psp\Runtime\String\StringJoin;
use PhpLisp\Psp\Scope;
use PhpLisp\Psp\Symbol;

class EnvironmentTest extends TestCase
{
    public function testSandbox()
    {
        $scope = Environment::sandbox();

        $this->assertInstanceOf(Scope::class, $scope);
        $this->assertNull($scope['nil']);
        $this->assertTrue($scope['true']);
        $this->assertTrue($scope['else']);
        $this->assertFalse($scope['false']);
        $this->assertTrue($scope['#t']);
        $this->assertFalse($scope['#f']);
        $this->assertInstanceOf(PspEval::class, $scope['eval']);
        $this->assertInstanceOf(Quote::class, $scope['quote']);
        $this->assertInstanceOf(PHPFunction::class, $scope['symbol']);

        /*        $this->assertEquals(

                    function (){
                      Symbol::get('');
                    },
                    $scope['symbol']->callback
                );*/
        $this->assertInstanceOf(Define::class, $scope['define']);
        $this->assertInstanceOf(Let::class, $scope['let']);
        $this->assertInstanceOf(Macro::class, $scope['macro']);
        $this->assertInstanceOf(Lambda::class, $scope['lambda']);
        $this->assertInstanceOf(Apply::class, $scope['apply']);
        $this->assertInstanceOf(PspDo::class, $scope['do']);
        $this->assertInstanceOf(Dict::class, $scope['dict']);
        $this->assertInstanceOf(PspArray::class, $scope['array']);
        $this->assertInstanceOf(PspList::class, $scope['list']);
        $this->assertInstanceOf(Car::class, $scope['car']);
        $this->assertInstanceOf(Cdr::class, $scope['cdr']);
        $this->assertInstanceOf(At::class, $scope['at']);
        $this->assertInstanceOf(SetAt::class, $scope['set-at!']);
        $this->assertInstanceOf(UnsetAt::class, $scope['unset-at!']);
        $this->assertInstanceOf(ExistsAt::class, $scope['exists-at?']);
        $this->assertInstanceOf(Count::class, $scope['count']);
        $this->assertInstanceOf(Map::class, $scope['map']);
        $this->assertInstanceOf(Filter::class, $scope['filter']);
        $this->assertInstanceOf(Fold::class, $scope['fold']);
        $this->assertInstanceOf(Cond::class, $scope['cond']);
        $this->assertInstanceOf(Eq::class, $scope['==']);
        $this->assertInstanceOf(Eq::class, $scope['eq']);
        $this->assertInstanceOf(Eq::class, $scope['eq?']);
        $this->assertInstanceOf(Equal::class, $scope['=']);
        $this->assertInstanceOf(Equal::class, $scope['equal']);
        $this->assertInstanceOf(Equal::class, $scope['equal?']);
        $this->assertInstanceOf(NotEq::class, $scope['/==']);
        $this->assertInstanceOf(NotEq::class, $scope['!==']);
        $this->assertInstanceOf(NotEq::class, $scope['not-eq']);
        $this->assertInstanceOf(NotEq::class, $scope['not-eq?']);
        $this->assertInstanceOf(NotEqual::class, $scope['!=']);
        $this->assertInstanceOf(NotEqual::class, $scope['/=']);
        $this->assertInstanceOf(
            NotEqual::class,
            $scope['not-equal']
        );
        $this->assertInstanceOf(
            NotEqual::class,
            $scope['not-equal?']
        );
        foreach (Type::$types as $type) {
            $this->assertInstanceOf(
                Type::class,
                $scope["$type?"]
            );
            $this->assertSame($type, $scope["$type?"]->type);
        }
        $this->assertInstanceOf(Type::class, $scope['nil?']);
        $this->assertSame('null', $scope['nil?']->type);
        $this->assertInstanceOf(IsA::class, $scope['is-a?']);
        $this->assertInstanceOf(IsA::class, $scope['isa?']);
        $this->assertInstanceOf(Addition::class, $scope['+']);
        $this->assertInstanceOf(Subtraction::class, $scope['-']);
        $this->assertInstanceOf(
            Multiplication::class,
            $scope['*']
        );
        $this->assertInstanceOf(Division::class, $scope['/']);
        $this->assertInstanceOf(Modulus::class, $scope['%']);
        $this->assertInstanceOf(Modulus::class, $scope['mod']);
        $this->assertInstanceOf(PHPFunction::class, $scope['string']);
        $this->assertSame('strval', $scope['string']->callback);
        $this->assertInstanceOf(Concat::class, $scope['.']);
        $this->assertInstanceOf(Concat::class, $scope['concat']);
        $this->assertInstanceOf(
            StringJoin::class,
            $scope['string-join']
        );
        $this->assertInstanceOf(PHPFunction::class, $scope['substring']);
        $this->assertSame('substr', $scope['substring']->callback);
        $this->assertInstanceOf(
            PHPFunction::class,
            $scope['string-upcase']
        );
        $this->assertSame('strtoupper', $scope['string-upcase']->callback);
        $this->assertInstanceOf(
            PHPFunction::class,
            $scope['string-downcase']
        );
        $this->assertSame('strtolower', $scope['string-downcase']->callback);
        $this->assertInstanceOf(Not::class, $scope['not']);
        $this->assertInstanceOf(PspAnd::class, $scope['and']);
        $this->assertInstanceOf(PspOr::class, $scope['or']);
        $this->assertInstanceOf(PspIf::class, $scope['if']);
        $this->assertInstanceOf(GetAttribute::class, $scope['->']);
    }

    public function testFull()
    {
        $scope = Environment::full();
        $this->testSandbox($scope);
        $this->assertInstanceOf(PspUse::class, $scope['use']);
        $this->assertInstanceOf(From::class, $scope['from']);
        $this->assertSame($_ENV, $scope['*env*']);
        $this->assertSame($_SERVER, $scope['*server*']);
    }
}
