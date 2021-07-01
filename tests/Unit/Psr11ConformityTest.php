<?php

namespace Tests\Unit;


use Elverion\DependencyInjection\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tests\Support\InstantiableClassWithoutParams;

class Psr11ConformityTest extends TestCase
{
    /** @var Container */
    private $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = Container::getInstance();
    }

    public function testContainerImplementsCorrectInterface()
    {
        self::assertTrue(in_array(ContainerInterface::class, class_implements(get_class($this->container))));
    }

    public function testContainerHasReturnsCorrectly()
    {
        self::assertFalse($this->container->has(InstantiableClassWithoutParams::class));

        $this->container->resolve(InstantiableClassWithoutParams::class);
        self::assertTrue($this->container->has(InstantiableClassWithoutParams::class));
    }

    public function testContainerGetAcceptsOnlyString()
    {
        $this->container->resolve(InstantiableClassWithoutParams::class);

        $this->container->get(InstantiableClassWithoutParams::class); // No exception

        self::expectError();
        $this->container->get(new InstantiableClassWithoutParams());
    }

    public function testContainerGetThrowsNotFoundExceptionInterfaceIfItemNotFound()
    {
        self::expectException(NotFoundExceptionInterface::class);
        $this->container->get('does not exist');
    }

}