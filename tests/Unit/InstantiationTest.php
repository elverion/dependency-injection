<?php

namespace Tests\Unit;


use Elverion\DependencyInjection\Container\Container;
use PHPUnit\Framework\TestCase;
use Tests\Support\InstantiableByInterfaceClass;
use Tests\Support\InstantiableClassGrandparent;
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
        $this->container = new Container();
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

    public function testCanMakeWithParameters()
    {
        $result = $this->container->make(InstantiableClassWithPrimitive::class, ['name' => 'Suzie', 'age' => 22]);

        self::assertSame('Suzie', $result->name);
        self::assertSame(22, $result->age);
    }

    /** @depends testResolveReturnsSingletonIfAvailable */
    public function testResolveWithParametersDoesNotUseSingleton()
    {
        $this->container->register(InstantiableClassWithPrimitive::class, new InstantiableClassWithPrimitive('Bob', 25));
        $result = $this->container->resolve(InstantiableClassWithPrimitive::class, ['name' => 'Suzie', 'age' => 22]);

        self::assertSame('Suzie', $result->name);
        self::assertSame(22, $result->age);
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

    public function testResolveReturnsSingletonIfAvailable()
    {
        $this->container->register(InstantiableClassWithoutParams::class, new InstantiableClassWithoutParams());
        $inst1 = $this->container->resolve(InstantiableClassWithoutParams::class);
        $inst2 = $this->container->resolve(InstantiableClassWithoutParams::class);

        self::assertSame($inst1, $inst2);
    }

    /**
     * @depends testResolveReturnsSingletonIfAvailable
     */
    public function testFlushRemovesResolvedInstances()
    {
        $this->container->register(InstantiableClassWithoutParams::class, new InstantiableClassWithoutParams());
        $inst1 = $this->container->resolve(InstantiableClassWithoutParams::class);

        $this->container->flush();
        self::assertFalse($this->container->has(InstantiableClassWithoutParams::class));
        $inst2 = $this->container->resolve(InstantiableClassWithoutParams::class);

        self::assertNotSame($inst1, $inst2);
    }

    public function testFlushRemovesBoundInstances()
    {
        $inst1 = new InstantiableClassWithoutParams();
        $this->container->bind(InstantiableClassWithoutParams::class, $inst1);
        self::assertTrue($this->container->has(InstantiableClassWithoutParams::class));

        $this->container->flush();
        self::assertFalse($this->container->has(InstantiableClassWithoutParams::class));
        $inst2 = $this->container->resolve(InstantiableClassWithoutParams::class);

        self::assertNotSame($inst1, $inst2);
    }

    public function testCanInstantiateWithSubObjectParams()
    {
        $inst = $this->container->make(InstantiableClassGrandparent::class, [
            'local' => 'grandparent',
            'child.other.default' => 'hello world',
            'child.local' => 'goodbye world',
        ]);
        self::assertSame('grandparent', $inst->local);
        self::assertSame('hello world', $inst->child->other->default);
        self::assertSame('goodbye world', $inst->child->local);
    }
}