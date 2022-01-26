<?php

namespace Tests\Unit;


use Elverion\DependencyInjection\Container\Container;
use PHPUnit\Framework\TestCase;
use Tests\Support\CallableClass;
use Tests\Support\InstantiableClassWithoutParams;
use Tests\Support\InstantiableOnlyWithUserIntervention;


function testFunctionCall(InstantiableClassWithoutParams $class, string $return): string
{
    return $return;
}

class CallTest extends TestCase
{
    /** @var Container */
    private $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = Container::getInstance();
    }

    public function testCanCallObjectMethod()
    {
        $inst = new CallableClass();
        $result = $this->container->call([$inst, 'call'], ['hello' => 'Hello World']);

        self::assertInstanceOf(InstantiableClassWithoutParams::class, $inst->other);
        self::assertSame('Hello World', $inst->hello);
        self::assertSame('bound method was called', $result);

        $dependency = new InstantiableOnlyWithUserIntervention(['item1' => 'yup']);
        $this->container->register(InstantiableOnlyWithUserIntervention::class, $dependency);
        $result = $this->container->call([$inst, 'call2']);
        self::assertSame('bound method was called', $result);
    }

    public function testCanCallClosure()
    {
        $callbackData = [];
        $callback = static function (InstantiableClassWithoutParams $other, string $hello) use (&$callbackData) {
            $callbackData['other'] = $other;
            $callbackData['hello'] = $hello;
            return 'closure was called';
        };

        $result = $this->container->call($callback, ['hello' => 'Hello World']);

        self::assertInstanceOf(InstantiableClassWithoutParams::class, $callbackData['other']);
        self::assertSame('Hello World', $callbackData['hello']);
        self::assertSame('closure was called', $result);
    }

    public function testCanCallFunction()
    {
        $result = $this->container->call('Tests\Unit\testFunctionCall', ['return' => 'function was called']);
        self::assertSame('function was called', $result);
    }
}