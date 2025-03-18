<?php

namespace Elverion\DependencyInjection\Exception;

use Throwable;

class BindNotFoundException extends NotFoundException
{
    public function __construct(
        $message = "A concrete binding was not found for the given abstract item.",
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}