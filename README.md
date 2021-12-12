# calc-fn
[![Build master](https://github.com/john-ask-later/calc-fn/actions/workflows/master.yml/badge.svg)](https://github.com/john-ask-later/calc-fn/actions/workflows/master.yml)
[![codecov](https://codecov.io/gh/john-ask-later/calc-fn/branch/master/graph/badge.svg)](https://codecov.io/gh/john-ask-later/calc-fn)

This package is result of working on test task

### test task
Write the function `calc($arg)` to bring this code to live:

~~~php
$sum = function ($a, $b) {return $a + $b;};
$sum2 = function ($a, $b, $c) {return $a + $b + $c;};

calc(5)(3)(2)($sum); // result: 10
calc(5)(3)(2)(1)(1)($sum2) // result: 12
calc(1)(2)($sum); // result: 3
calc(2)(3)('pow'); // result: 8
calc(2)(3)(2)('pow'); // result: 64

$f1 = calc(2)(2);
$f2 = calc(2)(3);
$f1('pow'); // result: 4
$f2('pow'); // result: 8
~~~

Try to cover possible runtime errors in your implementation

Add unit tests
