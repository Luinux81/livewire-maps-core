<?php

namespace LBCDev\LivewireMaps\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            \LBCDev\LivewireMaps\LivewireMapsServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configurar APP_KEY
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        // Configurar base de datos para los tests
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        // Configurar vistas del paquete
        $app['view']->addNamespace('livewire-maps', __DIR__ . '/../resources/views');
    }
}
