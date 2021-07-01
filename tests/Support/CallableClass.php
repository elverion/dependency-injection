<?php

namespace Tests\Support;

class CallableClass
{
    public $other;
    public $hello;

    public function call(InstantiableClassWithoutParams $other, string $hello)
    {
        $this->other = $other;
        $this->hello = $hello;

        return 'bound method was called';
    }
}