<?php

use Elverion\DependencyInjection\Container\Container;

require_once('../vendor/autoload.php');

interface Fetchable
{
    public function getName(): string;
}

class Stick implements Fetchable
{
    public function getName(): string
    {
        return 'stick';
    }
}

class Ball implements Fetchable
{
    public function getName(): string
    {
        return 'ball';
    }
}

class Nametag
{
    private string $name;
    private int $id;

    public function __construct(string $name, int $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

class Leash
{
    private string $color;

    public function __construct(string $color)
    {
        $this->color = $color;
    }

    public function getColor(): string
    {
        return $this->color;
    }
}

class DogWalker
{
    public function takeDog(Dog $dog, int $minutes = 30)
    {
        echo "I am taking {$dog->getName()} for a walk for {$minutes} minutes on a {$dog->getLeash()->getColor()} leash.\n";
    }
}

class Dog
{
    private $nametag;
    private $leash;

    public function __construct(Nametag $nametag, Leash $leash)
    {
        $this->nametag = $nametag;
        $this->leash = $leash;
    }

    public function getName(): string
    {
        return $this->nametag->getName();
    }

    public function getLeash(): Leash
    {
        return $this->leash;
    }

    public function fetch(Fetchable $fetchable)
    {
        echo "{$this->nametag->getName()} fetched a {$fetchable->getName()}\n";
    }

    public function walk(DogWalker $dogWalker)
    {
        $dogWalker->takeDog($this);
    }
}

$container = Container::getInstance();
$container->bind(Fetchable::class, Stick::class);
$container->bind(Nametag::class, new Nametag('Jack', 739481));

$dog = $container->resolve(Dog::class, ['leash.color' => 'red']);

// Should print: Jack fetched a stick
$container->call([$dog, 'fetch']);

// Should print: I am taking Jack for a walk for 30 minutes on a red leash.
$container->call([$dog, 'walk']);