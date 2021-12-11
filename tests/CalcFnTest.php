<?php

declare(strict_types=1);

namespace JohnAskLater\CalcFn\Tests;

use LogicException;
use PHPUnit\Framework\TestCase;
use Throwable;

use function JohnAskLater\CalcFn\calc;


class CalcFnTest extends TestCase
{
    public function test_fn_accepts_only_numbers_and_callbacks()
    {
        $bad = ['string', [], (object)[], '1,1'];
        foreach ($bad as $toCheck) {
            try {
                calc($toCheck);

                $this->fail('Bad value accepted');
            } catch (Throwable $e) {
                $this->assertLogicExceptionWithCode($e, 4);
            }
        }

        $good = [1, 1.1, '1', '1.1'];
        foreach ($good as $toCheck) {
            try {
                calc($toCheck);
            } catch (Throwable $e) {
                $this->fail('Exception on good value thrown');
            }
        }
    }

    public function test_fn_works_on_single_arg_callback()
    {
        $square = static function ($a) {
            return $a * $a;
        };

        // positive
        $this->assertEquals(4, calc(2)($square));
        $this->assertEquals(1, calc(1)($square));

        // Call with wrong params count leads to logic exception
        try {
            calc($square);

            $this->fail('Works without params');
        } catch (Throwable $e) {
            $this->assertLogicExceptionWithCode($e, 2);
        }

        try {
            calc(1)(2)($square);

            $this->fail('Works with more params than needed');
        } catch (Throwable $e) {
            $this->assertLogicExceptionWithCode($e, 2);
        }
    }

    public function test_fn_works_on_zero_arg_callback()
    {
        $getOne = static function () {
            return 1;
        };

        // Call with wrong callback leads to logic exception
        try {
            calc($getOne);

            $this->fail('Accept callback without params');
        } catch (Throwable $e) {
            $this->assertLogicExceptionWithCode($e, 5);
        }

        // Test that params count doesn't matter
        try {
            calc(1)(2)(3)($getOne);

            $this->fail('Accept callback without params');
        } catch (Throwable $e) {
            $this->assertLogicExceptionWithCode($e, 5);
        }
    }

    public function test_fn_works_on_callback_wich_require_more_that_two_params()
    {
        $sumThree = static function ($a, $b, $c) {
            return $a + $b + $c;
        };

        try {
            calc(1)(2)($sumThree);

            $this->fail('Works with invalid callback');
        } catch (Throwable $e) {
            $this->assertLogicExceptionWithCode($e, 1);
        }

        try {
            calc(1)(2)(3)(4)($sumThree);

            $this->fail('Works with invalid callback');
        } catch (Throwable $e) {
            $this->assertLogicExceptionWithCode($e, 0);
        }

        try {
            $this->assertNotEmpty(calc(1)(2)(3)(4)(5)($sumThree));
        } catch (Throwable $e) {
            $this->fail('Dont works with valid count of params');
        }

        $sum4 = static function ($a, $b, $c, $d) {
            return $a + $b + $c + $d;
        };

        try {
            calc(1)(2)(3)(4)(2)($sum4);

            $this->fail('Works with invalid callback');
        } catch (Throwable $e) {
            $this->assertLogicExceptionWithCode($e, 0);
        }

        try {
            calc(1)(2)(3)(4)(2)(1)($sum4);

            $this->fail('Works with invalid callback');
        } catch (Throwable $e) {
            $this->assertLogicExceptionWithCode($e, 0);
        }

        try {
            $this->assertNotEmpty(calc(1)(2)(3)(4)(5)(1)(1)($sum4));
        } catch (Throwable $e) {
            $this->fail('Dont works with valid count of params');
        }
    }

    public function test_calc_fn_calculations()
    {
        $sum = function ($a, $b) {
            return $a + $b;
        };

        // Test closure
        $this->assertEquals(10, calc(5)(3)(2)($sum));
        $this->assertEquals(3, calc(1)(2)($sum));

        // Test php internals
        $this->assertEquals(8, calc(2)(3)('pow'));
        $this->assertEquals(64, calc(2)(3)(2)('pow'));

        // Test class methods
        $this->assertEquals(10, calc(5)(3)(2)([$this, 'sum']));
        $this->assertEquals(3, calc(1)(2)([$this, 'sum']));

        // Test void fn
        $this->assertEmpty(calc(1)(2)([$this, 'sumVoid']));

        // Test saving temp results
        $f1 = calc(1)(2);
        $f2 = calc(2)(3);
        $r1 = $f1('pow');
        $r2 = $f2('pow');

        $this->assertEquals($r1, 1);
        $this->assertEquals($r2, 8);

        // Test on float and numeric strings
        $this->assertEquals(3.2, calc(1.1)(2.1)($sum));
        $this->assertEquals(10, calc('5')('3')('2')($sum));
        $this->assertEquals(3.2, calc('1.1')('2.1')($sum));

        // Test with callbacks for few params
        $sum3 = function ($a, $b, $c) {
            return $a + $b + $c;
        };

        $sum4 = function ($a, $b, $c, $d) {
            return $a + $b + $c + $d;
        };

        $this->assertEquals(15, calc(1)(2)(3)(4)(5)($sum3));
        $this->assertEquals(6, calc(1)(2)(3)($sum3));
        $this->assertEquals(9, calc(1)(1)(1)(1)(1)(2)(2)($sum3));

        $this->assertEquals(10, calc(1)(1)(1)(1)(2)(2)(2)($sum4));

        // test that needed params can de added via temp variables
        $v1 = calc(1)(1)(1);
        $v2 = $v1(1)(2)(2)(2);
        $r3 = $v2($sum4);

        $this->assertEquals(10, $r3);
    }

    public function sum($a, $b)
    {
        return $a + $b;
    }

    public function sumVoid($a, $b): void
    {
        // Nothing to do here
    }

    protected function assertLogicExceptionWithCode(Throwable $e, int $code)
    {
        $this->assertEquals($code, $e->getCode(), 'Invalid exception code');
        $this->assertEquals(LogicException::class, get_class($e), 'Invalid exception class');
    }
}