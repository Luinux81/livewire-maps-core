<?php

namespace LBCDev\LivewireMaps\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            // Aquí registrarás el ServiceProvider de tu paquete en el futuro
            // \YourNamespace\Core\CoreServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configuración de base de datos para los tests
        $app['config']->set('database.default', 'testing');
    }
}
