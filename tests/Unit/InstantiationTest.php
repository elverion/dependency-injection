<?php

namespace Tests\Unit;


use Elverion\DependencyInjection\Container\Container;
use PHPUnit\Framework\TestCase;
use Tests\Support\InstantiableByInterfaceClass;
use Tests\Support\InstantiableClassInterface;
use Tests\Support\InstantiableClassWithDefaultParams;
use Tests\Support\InstantiableClassWithDependency;
use Tests\Support\InstantiableClassWithoutParams;
use Tests\Support\InstantiableClassWithPrimitive;

class InstantiationTest extends TestCase
{
    /** @var Container */
    private $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = Container::getInstance();
    }

    public function testCanInstantiate()
    {
        self::assertInstanceOf(Container::class, $this->container);
    }

    public function testCanMakeWithoutParams()
    {
        $result = $this->container->make(InstantiableClassWithoutParams::class);
        self::assertInstanceOf(InstantiableClassWithoutParams::class, $result);
    }

    public function testCanMakeWithDefaultParams()
    {
        $result = $this->container->make(InstantiableClassWithDefaultParams::class);
        self::assertInstanceOf(InstantiableClassWithDefaultParams::class, $result);
        self::assertSame('default', $result->default);
    }

    public function testCanMakeWithDependency()
    {
        $result = $this->container->make(InstantiableClassWithDependency::class);
        self::assertInstanceOf(InstantiableClassWithDependency::class, $result);
        self::assertInstanceOf(InstantiableClassWithoutParams::class, $result->other);
    }

    public function testCanMakeWithPrimitive()
    {
        $this->container->bind('name', 'Bob');
        $this->container->bind('age', 25);

        $result = $this->container->make(InstantiableClassWithPrimitive::class);
        self::assertSame('Bob', $result->name);
        self::assertSame(25, $result->age);
    }

    public function testCanMakeFromBind()
    {
        $this->container->bind(InstantiableClassInterface::class, InstantiableByInterfaceClass::class);
        $result = $this->container->make(InstantiableClassInterface::class);

        self::assertInstanceOf(InstantiableByInterfaceClass::class, $result);
    }

    public function testCanMakeFromClosure()
    {
        $this->container->bind(InstantiableClassInterface::class, static function () {
            return new InstantiableByInterfaceClass();
        });

        $result = $this->container->make(InstantiableClassInterface::class);
        self::assertInstanceOf(InstantiableByInterfaceClass::class, $result);
    }
}