<?php

namespace Tests\Support;

class InstantiableClassGrandparent
{
    public $child;
    public $local;

    public function __construct(InstantiableClassWithDependencyWithParams $child, string $local)
    {
        $this->child = $child;
        $this->local = $local;
    }
}