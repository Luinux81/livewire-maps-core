<?php

namespace LBCDev\LivewireMaps\Tests\Feature;

use LBCDev\LivewireMaps\Tests\TestCase;

class ExampleFeatureTest extends TestCase
{
    public function test_it_can_access_laravel_features()
    {
        // Prueba que el entorno de testing funciona
        $this->assertEquals('testing', config('database.default'));
    }
}
