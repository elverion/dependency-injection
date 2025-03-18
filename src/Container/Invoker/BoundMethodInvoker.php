<?php

namespace Elverion\DependencyInjection\Container\Invoker;

use Elverion\DependencyInjection\Container\ContainerContract;
use ReflectionMethod;

class BoundMethodInvoker implements InvokerInterface
{
    private object $class;
    private ContainerContract $container;
    private ReflectionMethod $reflection;

    public function __construct(ContainerContract $container, object $class, ?string $method)
    {
        $this->class = $class;
        $this->container = $container;
        $this->reflection = new ReflectionMethod($class, $method === null ? '__invoke' : $method);
    }

    public function invoke(array $params)
    {
        return $this->reflection->invokeArgs(
            $this->class,
            $this->container->getMethodDependencies($this->reflection, $params)
        );
    }
}