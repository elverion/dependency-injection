<?php

namespace Elverion\DependencyInjection\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;
use Throwable;

class UnresolvableDependencyException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct($message = 'Cannot resolve dependency', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}