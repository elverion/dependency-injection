<?php

namespace Elverion\DependencyInjection\Container\Invoker;

use Closure;
use Elverion\DependencyInjection\Container\ContainerContract;
use InvalidArgumentException;

class InvokerFactory
{
    public static function make(ContainerContract $container, $what)
    {
        if ($what instanceof Closure) {
            /** @var Closure $what */
            return new ClosureInvoker($container, $what);
        }

        if (is_string($what) && function_exists($what)) {
            return new FunctionInvoker($container, $what);
        }

        if (is_array($what)) {
            /** @var array $what */
            return new BoundMethodInvoker($container, $what[0], $what[1] ?? null);
        }

        $type = gettype($what);
        throw new InvalidArgumentException("Expected closure, function, or bound method; received {$type}");
    }
}