<?php

namespace Example\NamespaceTest;

// Intentional spaces between `use` and fully qualified namespace for regex testing
// Intentional leading backslash
use       \Illuminate\Support\ServiceProvider;

class ValidServiceProviderWithExtraSpacesAndLeadingBackslash extends ServiceProvider
{
    public function exampleMethod()
    {

    }
}