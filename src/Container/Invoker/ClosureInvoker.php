<?php

namespace Elverion\DependencyInjection\Container\Invoker;

use Closure;
use Elverion\DependencyInjection\Container\ContainerContract;
use ReflectionFunction;

class ClosureInvoker implements InvokerInterface
{
    protected ContainerContract $container;
    protected ReflectionFunction $reflection;

    public function __construct(ContainerContract $container, Closure $closure)
    {
        $this->container = $container;
        $this->reflection = new ReflectionFunction($closure);
    }

    public function invoke(array $params)
    {
        return $this->reflection->invokeArgs($this->container->getMethodDependencies($this->reflection, $params));
    }
}