<?php

declare(strict_types=1);

namespace JohnAskLater\CalcFn;

use Closure;
use LogicException;
use ReflectionFunction;
use ReflectionMethod;
use function array_merge;
use function call_user_func;
use function call_user_func_array;
use function count;
use function is_callable;

/**
 * @param callable|numeric $arg
 *
 * @return Closure|numeric
 * @throws \ReflectionException
 */
function calc($arg)
{
    $internal = static function ($arg, array $values) use (&$internal) {
        // Add argument to chain
        if (is_numeric($arg)) {
            $values[] = $arg;

            return static function ($arg) use ($values, $internal) {
                return $internal($arg, $values);
            };
        }

        // Here incoming $arg must have callable type only
        if (!is_callable($arg)) {
            $message = 'Argument must be either number or callable';
            throw new LogicException($message, 4);
        }

        // Check number of required params
        if (is_array($arg)) {
            $reflection = new ReflectionMethod(...$arg);
        } else {
            $reflection = new ReflectionFunction($arg);
        }

        $reqNum = $reflection->getNumberOfRequiredParameters();
        $valNum = count($values);

        // Corner case when callback will not accept parameters
        if ($reqNum === 0) {
            $message = 'Callback must have at least one required parameter';
            throw new LogicException($message, 5);
        }

        // Corner case when callback expects only one parameter: in this way we can't use chain of calls
        if ($reqNum === 1) {
            if ($valNum !== 1) {
                $message = 'Callback which require one parameter must be passed exactly after 1 number has been provided';
                throw new LogicException($message, 2);
            }

            return call_user_func($arg, $values[0]);
        }

        // Corner case when we haven't enough params
        if ($reqNum > $valNum) {
            $message = "Not enough data to calculate. You must pass at least {$reqNum} numeric value before processing";
            throw new LogicException($message, 1);
        }

        // Validate that incoming parameters count is suitable for callback requirements
        if (($valNum - 1) % ($reqNum - 1) !== 0) {
            $multiple = $reqNum - 1;
            $message = "Callback expects {$reqNum} of params, count of args must be a multiple of: {$multiple}";
            throw new LogicException($message, 0);
        }

        // Process arguments
        $toCallback = array_splice($values, 0, $reqNum);
        while ($toCallback) {
            // Get result of call
            $result = call_user_func_array($arg, $toCallback);

            // Add next $requiredArgs - 1 params and call again
            if (count($values) > 0) {
                $toCallback = array_merge([$result], array_splice($values, 0, $reqNum - 1));
            } else {
                $toCallback = null;
            }
        }

        return $result;
    };

    return $internal($arg, []);
}
