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

class DogWalker
{
    public function takeDog(Dog $dog, int $minutes = 30)
    {
        echo "I am taking {$dog->getName()} for a walk for {$minutes} minutes\n";
    }
}

class Dog
{
    private $nametag;

    public function __construct(Nametag $nametag)
    {
        $this->nametag = $nametag;
    }

    public function getName(): string
    {
        return $this->nametag->getName();
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

$dog = $container->resolve(Dog::class);

// Should print: Jack fetched a stick
$container->call([$dog, 'fetch']);

// Should print: I am taking Jack for a walk for 30
$container->call([$dog, 'walk']);