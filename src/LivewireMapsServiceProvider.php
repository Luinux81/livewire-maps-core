<?php

namespace LBCDev\LivewireMaps;

use Illuminate\Support\ServiceProvider;
use LBCDev\LivewireMaps\Components\LivewireMap;
use Livewire\Livewire;

class LivewireMapsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/livewire-maps.php',
            'livewire-maps'
        );
    }

    public function boot(): void
    {
        // Registrar componentes Livewire con alias explícito
        Livewire::component('livewire-map', LivewireMap::class);

        // Publicar configuración
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/livewire-maps.php' => $this->app->configPath('livewire-maps.php'),
            ], 'livewire-maps-config');

            // Publicar vistas
            $this->publishes([
                __DIR__ . '/../resources/views' => $this->app->resourcePath('views/vendor/livewire-maps'),
            ], 'livewire-maps-views');
        }

        // Cargar vistas
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'livewire-maps');
    }
}
