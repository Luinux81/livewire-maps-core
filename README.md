# Livewire Maps Core

[![Tests](https://github.com/Luinux81/livewire-maps-core/workflows/Tests/badge.svg)](https://github.com/Luinux81/livewire-maps-core/actions)
[![Latest Stable Version](https://poser.pugx.org/lbcdev/livewire-maps-core/v)](https://packagist.org/packages/lbcdev/livewire-maps-core)
[![Total Downloads](https://poser.pugx.org/lbcdev/livewire-maps-core/downloads)](https://packagist.org/packages/lbcdev/livewire-maps-core)
[![License](https://poser.pugx.org/lbcdev/livewire-maps-core/license)](https://packagist.org/packages/lbcdev/livewire-maps-core)

A Livewire component for interactive maps with Leaflet.js integration. Core package for the LBCDev Maps Suite.

## Features

- 🗺️ **Interactive Maps**: Full Leaflet.js integration via Alpine.js
- 🎯 **Single & Multi Marker**: Support for single markers or marker collections
- 🔄 **Reactive Updates**: Real-time map updates with Livewire
- 📍 **Coordinate Validation**: Built-in validation for latitude/longitude
- ⚡ **Events System**: Emit and listen to map events
- 🎨 **Customizable**: Extensive configuration options
- ✅ **Well Tested**: 13 comprehensive tests
- 📚 **Fully Documented**: Complete API documentation

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- Livewire 3.x
- [lbcdev/map-geometries](https://github.com/Luinux81/map-geometries)

## Installation

```bash
composer require lbcdev/livewire-maps-core
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="livewire-maps-config"
```

## Quick Start

### Basic Usage

```blade
@livewire('livewire-map', [
    'center' => ['lat' => 40.7128, 'lng' => -74.0060],
    'zoom' => 13
])
```

### With Single Marker

```php
use LBCDev\MapGeometries\Marker;

$marker = Marker::make(40.7128, -74.0060, 'New York City')
    ->tooltip('The Big Apple')
    ->iconColor('blue');
```

```blade
@livewire('livewire-map', [
    'marker' => $marker,
    'center' => ['lat' => 40.7128, 'lng' => -74.0060],
    'zoom' => 13
])
```

### With Multiple Markers

```php
use LBCDev\MapGeometries\Marker;
use LBCDev\MapGeometries\MarkerCollection;

$markers = new MarkerCollection();

$markers->add(
    Marker::make(40.7128, -74.0060, 'New York')
        ->iconColor('blue')
);

$markers->add(
    Marker::make(51.5074, -0.1278, 'London')
        ->iconColor('red')
);
```

```blade
@livewire('livewire-map', [
    'markers' => $markers,
    'center' => ['lat' => 40.7128, 'lng' => -74.0060],
    'zoom' => 3
])
```

## Configuration

The package comes with sensible defaults, but you can customize everything:

```php
// config/livewire-maps.php

return [
    'default_center' => [
        'lat' => env('LIVEWIRE_MAPS_DEFAULT_LAT', 0),
        'lng' => env('LIVEWIRE_MAPS_DEFAULT_LNG', 0),
    ],
    
    'default_zoom' => env('LIVEWIRE_MAPS_DEFAULT_ZOOM', 10),
    
    'tile_layer' => [
        'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'attribution' => '&copy; OpenStreetMap contributors',
    ],
    
    'default_options' => [
        'scrollWheelZoom' => true,
        'dragging' => true,
        'zoomControl' => true,
    ],
];
```

## Component API

### Properties

| Property | Type | Default | Description |
| -------- | ---- | ------- | ----------- |
| `$marker` | `Marker\|null` | `null` | Single marker to display |
| `$markers` | `MarkerCollection\|null` | `null` | Collection of markers |
| `$center` | `array` | config | Map center `['lat' => X, 'lng' => Y]` |
| `$zoom` | `int` | config | Initial zoom level |
| `$height` | `string` | `'500px'` | Map container height |
| `$interactive` | `bool` | `true` | Enable/disable interactions |
| `$options` | `array` | config | Leaflet map options |

### Methods

| Method | Parameters | Description |
| ------ | ---------- | ----------- |
| `addMarker()` | `Marker $marker` | Add a marker to the map |
| `removeMarker()` | `string $id` | Remove a marker by ID |
| `clearMarkers()` | - | Remove all markers |
| `flyTo()` | `float $lat, float $lng, int $zoom` | Center map with animation |

### Computed Properties

| Property | Type | Description |
| -------- | ---- | ----------- |
| `markersData` | `array` | Get all markers as array |
| `hasCoordinates` | `bool` | Check if valid coordinates |

### Events

#### Emitted Events

```php
// Coordinates updated
$this->dispatch('map-coordinates-updated', [
    'lat' => 40.7128,
    'lng' => -74.0060
]);
```

#### Listening to Events

```php
protected $listeners = [
    'fly-to-coordinates' => 'flyTo',
];

public function flyTo(array $data)
{
    // $data = ['lat' => X, 'lng' => Y, 'zoom' => Z]
}
```

## Advanced Usage

### Custom Map Options

```blade
@livewire('livewire-map', [
    'center' => ['lat' => 40.7128, 'lng' => -74.0060],
    'zoom' => 13,
    'options' => [
        'scrollWheelZoom' => false,
        'minZoom' => 10,
        'maxZoom' => 18,
        'maxBounds' => [
            [40.5, -74.5],
            [40.9, -73.5]
        ],
    ]
])
```

### Read-Only Mode

```blade
@livewire('livewire-map', [
    'marker' => $marker,
    'interactive' => false
])
```

### Custom Height

```blade
@livewire('livewire-map', [
    'center' => ['lat' => 40.7128, 'lng' => -74.0060],
    'height' => '700px'
])
```

### Legacy Mode (Separate lat/lng)

For backward compatibility:

```blade
@livewire('livewire-map', [
    'latitude' => 40.7128,
    'longitude' => -74.0060
])
```

## Using in Livewire Components

```php
use Livewire\Component;
use LBCDev\MapGeometries\Marker;
use LBCDev\MapGeometries\MarkerCollection;

class LocationPicker extends Component
{
    public ?Marker $selectedLocation = null;
    public MarkerCollection $locations;
    
    protected $listeners = [
        'map-coordinates-updated' => 'handleLocationSelected'
    ];
    
    public function mount()
    {
        $this->locations = new MarkerCollection();
        
        // Add some locations
        $this->locations->add(
            Marker::make(40.7128, -74.0060, 'New York')
        );
    }
    
    public function handleLocationSelected($data)
    {
        $this->selectedLocation = Marker::make(
            $data['lat'],
            $data['lng'],
            'Selected Location'
        );
    }
    
    public function render()
    {
        return view('livewire.location-picker');
    }
}
```

## Testing

```bash
composer test
```

With coverage:

```bash
composer test-coverage
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email <luinux81@gmail.com> instead of using the issue tracker.

## Credits

- [Luinux81](https://github.com/Luinux81)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Related Packages

This package is part of the LBCDev Maps Suite:

- [map-geometries](https://github.com/Luinux81/map-geometries) - Map geometry classes (Marker, Polyline, etc.)
- [filament-maps-fields](https://github.com/Luinux81/filament-maps-fields) - Map form fields for Filament
- [filament-maps-widgets](https://github.com/Luinux81/filament-maps-widgets) - Map widgets for Filament panels
- [lbcdev-filament-maps-suite](https://github.com/Luinux81/lbcdev-filament-maps-suite) - Meta-package for all components
