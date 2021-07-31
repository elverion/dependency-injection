<?php

namespace Tests\Support;

class InstantiableClassWithDependencyWithParams
{
    public $other;
    public $local;

    public function __construct(InstantiableClassWithDefaultParams $other, string $local)
    {
        $this->other = $other;
        $this->local = $local;
    }
}