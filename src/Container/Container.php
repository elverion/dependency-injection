<?php

namespace Elverion\DependencyInjection\Container;

use Closure;
use Elverion\DependencyInjection\Container\Invoker\InvokerFactory;
use Elverion\DependencyInjection\Exception\BindNotFoundException;
use Elverion\DependencyInjection\Exception\NotFoundException;
use Elverion\DependencyInjection\Exception\UnresolvableDependencyException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Reflector;

class Container implements ContainerContract
{
    protected static ?ContainerContract $instance = null; // Singleton

    protected array $resolved = []; // Types that have been resolved and may be re-used
    protected array $binds = []; // User-specified bindings from abstract to concrete implementations

    /**
     * Returns the singleton
     *
     * @return ContainerContract|null
     */
    public static function getInstance(): ?ContainerContract
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Allows overriding the singleton instance
     * Returns the same instance for method chaining.
     *
     * @param ContainerContract $instance
     * @return ContainerContract
     */
    public static function setInstance(ContainerContract $instance): ContainerContract
    {
        static::$instance = $instance;
        return $instance;
    }

    /**
     * Call a method, but with injected dependencies!
     *
     * @param $callback
     * @param array $parameters
     * @param string $defaultMethod
     * @return mixed
     */
    public function call($callback, array $parameters = [], $defaultMethod = '__invoke')
    {
        $invoker = InvokerFactory::make($this, $callback);
        return $invoker->invoke($parameters);
    }

    /**
     * Returns the dependencies in key-value pairs for a method
     *
     * @param Reflector $reflector
     * @param array $parameters
     * @return array
     * @throws ReflectionException
     */
    public function getMethodDependencies(Reflector $reflector, array $parameters = []): array
    {
        $resolvedParams = [];
        foreach ($reflector->getParameters() as $param) {
            if (array_key_exists($param->getName(), $parameters)) {
                // Use the value supplied by user
                $resolvedParams[$param->getName()] = $parameters[$param->getName()];
            } else {
                // Resolve it
                $resolvedParams[$param->getName()] = $this->resolveDependency($param);
            }
        }

        return $resolvedParams;
    }

    /**
     * Makes a new instance of an item with dependency injection.
     * This does *not* store the resolved item for future use!
     *
     * @param string $fqn
     * @return mixed|object
     * @throws ReflectionException
     */
    public function make(string $fqn)
    {
        $reflection = new ReflectionClass($fqn);

        if ($reflection->isInterface()) {
            if ($this->hasBind($fqn)) {
                return $this->resolveBind($fqn);
            } else {
                throw new UnresolvableDependencyException();
            }
        }

        if (!$reflection->isInstantiable()) {
            throw new UnresolvableDependencyException();
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $reflection->newInstanceArgs();
        }

        $params = $constructor->getParameters();
        $resolvedParams = [];

        // Resolve each dependency
        foreach ($params as $param) {
            $resolvedParams[$param->getName()] = $this->resolveDependency($param);
        }

        return $reflection->newInstanceArgs($resolvedParams);
    }

    /**
     * Returns an already-resolved item if one exists, or resolves and returns it.
     * Stores the resolution for future use such that additional calls will
     * return the previously resolved item.
     *
     * @param string $id
     * @return mixed
     * @throws ReflectionException
     */
    public function resolve(string $id)
    {
        if ($this->has($id)) {
            return $this->resolved[$id];
        }

        $this->resolved[$id] = $this->make($id);
        return $this->resolved[$id];
    }

    /**
     * Resolves a reflected parameter if possible, or throws if it could not be resolved
     *
     * @param ReflectionParameter $dependency
     * @return mixed|object|null
     * @throws ReflectionException
     */
    protected function resolveDependency(ReflectionParameter $dependency)
    {
        $type = $dependency->getType();

        // Bound by type; for example, a concrete class bound to an interface
        if ($this->hasBind($type->getName())) {
            return $this->resolveBind($type->getName());
        }

        // Construct from FQCN
        if (class_exists($type->getName())) {
            return $this->make($type->getName());
        }

        if ($type->isBuiltin()) {
            // Bound by name; for example a named variable bound to a specific value
            if ($this->hasBind($dependency->getName())) {
                return $this->binds[$dependency->getName()];
            }
        }

        // Else, try to use a default
        if ($dependency->isDefaultValueAvailable()) {
            return $dependency->getDefaultValue();
        }

        // If no default, but null is available, use that
        if ($type->allowsNull()) {
            return null;
        }

        throw new UnresolvableDependencyException();
    }

    /**
     * Binds a concrete implementation to an abstract
     *
     * @param string $abstract
     * @param string|Closure $concrete
     */
    public function bind(string $abstract, $concrete): void
    {
        $this->binds[$abstract] = $concrete;
    }

    /**
     * Returns true if a bind for the abstract exists, otherwise returns false.
     *
     * @param string $abstract
     * @return bool
     */
    public function hasBind(string $abstract): bool
    {
        return array_key_exists($abstract, $this->binds);
    }

    /**
     * Resolves a concrete implementation from an abstract
     *
     * @param string $abstract
     * @return mixed|object
     * @throws ReflectionException
     * @throws BindNotFoundException
     */
    protected function resolveBind(string $abstract)
    {
        if (!array_key_exists($abstract, $this->binds)) {
            throw new BindNotFoundException();
        }

        $resolved = $this->binds[$abstract];

        if (is_string($resolved)) {
            return $this->make($resolved);
        }

        if ($resolved instanceof Closure) {
            return $resolved($this);
        }

        return $resolved;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException();
        }

        return $this->resolved[$id];
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->resolved);
    }
}