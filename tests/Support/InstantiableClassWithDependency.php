<?php

namespace Tests\Support;

class InstantiableClassWithDependency
{
    public $other;

    public function __construct(InstantiableClassWithoutParams $other)
    {
        $this->other = $other;
    }
}