<?php

namespace Elverion\DependencyInjection\Container;

use Psr\Container\ContainerInterface;
use Reflector;

interface ContainerContract extends ContainerInterface
{
    /**
     * Returns the singleton
     *
     * @return ContainerContract|null
     */
    public static function getInstance(): ?ContainerContract;

    /**
     * Allows overriding the singleton instance
     * Returns the same instance for method chaining.
     *
     * @param ContainerContract $instance
     * @return ContainerContract
     */
    public static function setInstance(ContainerContract $instance): ContainerContract;

    /**
     * Call a method, but with injected dependencies!
     *
     * @param $callback
     * @param array $parameters
     * @param string $defaultMethod
     * @return mixed
     */
    public function call($callback, array $parameters = [], $defaultMethod = '__invoke');

    /**
     * Returns the dependencies in key-value pairs for a method
     *
     * @param Reflector $reflector
     * @param array $parameters
     * @return array
     */
    public function getMethodDependencies(Reflector $reflector, array $parameters = []): array;

    /**
     * Makes a new instance of an item with dependency injection.
     *
     * User-defined parameters can be passed in by an array in
     * key-value form, where the key should be the name of the
     * parameter.
     *
     * The parameters array will *not* be forwarded to
     * dependencies (for now, planned for future).
     *
     * @param string $fqn
     * @param array $parameters
     * @return mixed|object
     */
    public function make(string $fqn, array $parameters = []);

    /**
     * Returns a registered singleton if available, or makes
     * and returns one.
     *
     * See make() for more information.
     *
     * @param string $id
     * @param array $parameters
     * @return mixed
     */
    public function resolve(string $id, array $parameters = []);

    /**
     * Register a singleton
     *
     * @param string $id
     * @param $concrete
     */
    public function register(string $id, $concrete): void;

    /**
     * Returns whether or not a singleton has been registered.
     *
     * @param string $id
     * @return bool
     */
    public function isRegistered(string $id): bool;

    /**
     * Binds a concrete implementation to an abstract
     *
     * @param string $abstract
     * @param string|\Closure $concrete
     */
    public function bind(string $abstract, $concrete): void;

    /**
     * Returns true if a bind for the abstract exists, otherwise returns false.
     *
     * @param string $abstract
     * @return bool
     */
    public function hasBind(string $abstract): bool;

    /**
     * Forget all binds, resolutions, etc.
     * @return void
     */
    public function flush(): void;
}