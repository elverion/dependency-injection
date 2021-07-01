### About

This project can be used to aid in [dependency injection](https://en.wikipedia.org/wiki/Dependency_injection) in your
own projects. The implementation, which is PSR-11 container compliant, is as bare-bones as can be without compromising
on functionality. It is capable of resolving concrete implementations for classes, interfaces, and bindings, and
primitives, as well as invoking closures, functions, and bound methods with their dependencies.

### Requirements
|php| \>= 7.4 |
|---|--------|



### Installation

##### Composer
```bash
composer require elverion/dependency-injection
```

### Usage

It is as simple as obtaining the instance of the container object and making use of the `->make()` or `->resolve()`
methods (to obtain a concrete implementation of some abstract) or use `->call()` to call a method and inject
dependencies into it. For example:
```php
<?php

use Elverion\DependencyInjection\Container\Container;

class MyApp {
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    // ...
}

class Task {
    public function handle(MyApp $app) {
        // ...
    }
}



$container = Container::getInstance();

$task = new Task();
$container->call([$task, 'handle']); // Injects a Client into MyApp, then calls Task::handle() with the MyApp instance
```

Also see examples/example.php for a more in-depth example.