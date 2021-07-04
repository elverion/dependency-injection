<?php

namespace Elverion\DependencyInjection\Container;

use Closure;
use Elverion\DependencyInjection\Container\Invoker\InvokerFactory;
use Elverion\DependencyInjection\Exception\BindNotFoundException;
use Elverion\DependencyInjection\Exception\NotFoundException;
use Elverion\DependencyInjection\Exception\UnresolvableDependencyException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Reflector;

class Container implements ContainerContract
{
    protected static ?ContainerContract $instance = null; // Singleton

    protected array $binds = []; // User-specified bindings from abstract to concrete implementations
    protected array $resolved = []; // Types that have been resolved
    protected array $instances = []; // Singletons that have been registered and may be re-used

    /** @inheritDoc */
    public static function getInstance(): ?ContainerContract
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /** @inheritDoc */
    public static function setInstance(ContainerContract $instance): ContainerContract
    {
        static::$instance = $instance;
        return $instance;
    }

    /** @inheritDoc */
    public function call($callback, array $parameters = [], $defaultMethod = '__invoke')
    {
        $invoker = InvokerFactory::make($this, $callback);
        return $invoker->invoke($parameters);
    }

    /** @inheritDoc */
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

    /** @inheritDoc */
    public function make(string $fqn, array $parameters = [])
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
            $resolvedParams[$param->getName()]
                = $parameters[$param->getName()] ?? $this->resolveDependency($param);
        }

        return $reflection->newInstanceArgs($resolvedParams);
    }

    /** @inheritDoc */
    public function resolve(string $id, array $parameters = [])
    {
        // If there are user-controlled parameters passed in, we can't rely on
        // a singleton here; we'll have to construct an all-new instance.
        if ($this->isRegistered($id) && empty($parameters)) {
            return $this->instances[$id];
        }

        $item = $this->make($id, $parameters);

        $this->resolved[$id] = true;
        return $item;
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

    /** @inheritDoc */
    public function register(string $id, $concrete): void
    {
        $this->instances[$id] = $concrete;
    }

    /** @inheritDoc */
    public function isRegistered(string $id): bool
    {
        return array_key_exists($id, $this->instances);
    }

    /** @inheritDoc */
    public function bind(string $abstract, $concrete): void
    {
        $this->binds[$abstract] = $concrete;
    }

    /** @inheritDoc */
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

    /** @inheritDoc */
    public function flush(): void
    {
        $this->binds = [];
        $this->resolved = [];
        $this->instances = [];
    }

    /** @inheritDoc */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException();
        }

        return $this->resolve($id);
    }

    /** @inheritDoc */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->instances)
            || array_key_exists($id, $this->resolved)
            || array_key_exists($id, $this->binds);
    }
}