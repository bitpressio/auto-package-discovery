<?php

namespace Example\NamespaceTest;

// Intentional spaces between `use` and fully qualified namespace for regex testing
use       Illuminate\Support\ServiceProvider;

class ValidServiceProviderWithExtraSpaces extends ServiceProvider
{
    public function exampleMethod()
    {

    }
}