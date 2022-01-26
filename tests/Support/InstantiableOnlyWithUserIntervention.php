<?php

namespace Tests\Support;

class InstantiableOnlyWithUserIntervention
{
    public array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
}