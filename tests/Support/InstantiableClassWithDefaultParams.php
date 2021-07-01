<?php

namespace Tests\Support;

class InstantiableClassWithDefaultParams
{
    public string $default;

    public function __construct(string $default = 'default')
    {
        $this->default = $default;
    }
}