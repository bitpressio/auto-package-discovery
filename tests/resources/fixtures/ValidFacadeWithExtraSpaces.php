<?php

namespace Example\NamespaceTest;

// Intentional spaces between `use` and fully qualified namespace for regex testing
use       \Illuminate\Support\Facades\Facade;

class ValidFacadeWithExtraSpaces extends Facade
{
    public function exampleMethod()
    {

    }
}