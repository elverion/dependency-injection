<?php

namespace Elverion\DependencyInjection\Container\Invoker;

use DomainException;
use Elverion\DependencyInjection\Container\ContainerContract;
use ReflectionFunction;

class FunctionInvoker extends ClosureInvoker implements InvokerInterface
{
    protected ContainerContract $container;
    protected ReflectionFunction $reflection;

    public function __construct(ContainerContract $container, string $function)
    {
        if (!function_exists($function)) {
            throw new DomainException("\$function was expected to be a function name but no such function could be found; received `{$function}`");
        }

        $this->container = $container;
        $this->reflection = new ReflectionFunction($function);
    }
}