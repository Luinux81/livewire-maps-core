# Livewire Maps Core

Un componente Livewire flexible e interactivo para mapas usando Leaflet.js con soporte completo para geometrías.

## Características

- 🗺️ Mapas interactivos con Leaflet.js
- 📍 Soporte para Markers individuales y colecciones
- 🎯 Click en el mapa para colocar marcadores
- 📋 Entrada manual de coordenadas
- 🎨 Soporte para modo claro/oscuro
- 🔒 Modo de solo lectura opcional
- ⚡ Eventos Livewire para integración con otros componentes
- 🔧 Configuración centralizada
- 🧩 Integración con `lbcdev/map-geometries`
- 🔄 Retrocompatibilidad con modo legacy (lat/lng)

## Requisitos

- PHP 8.1 o superior
- Laravel 10.x, 11.x o 12.x
- Livewire 3.x
- `lbcdev/map-geometries` (instalado automáticamente)

## Instalación

### 1. Instalar el paquete via Composer

```bash
composer require lbcdev/livewire-maps-core
```

### 2. Incluir Leaflet.js en tu layout

Agrega estos scripts en el `<head>` de tu layout principal (antes de `@livewireStyles`):

```html
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
```

### 3. (Opcional) Publicar configuración y vistas

Publicar configuración:

```bash
php artisan vendor:publish --tag=livewire-maps-config
```

Publicar vistas:

```bash
php artisan vendor:publish --tag=livewire-maps-views
```

Las vistas se publicarán en `resources/views/vendor/livewire-maps/`.

## Uso Básico

### Modo Simple (sin coordenadas)

```blade
<livewire:livewire-map />
```

### Modo Legacy (coordenadas directas)

```blade
<livewire:livewire-map
    :latitude="40.416775"
    :longitude="-3.703790"
/>
```

### Modo Geometrías (recomendado)

```blade
@php
use LBCDev\MapGeometries\Marker;

$marker = Marker::make(40.416775, -3.703790)
    ->label('Madrid')
    ->tooltip('Capital de España');
@endphp

<livewire:livewire-map :marker="$marker" />
```

### Múltiples Marcadores

```blade
@php
use LBCDev\MapGeometries\Marker;
use LBCDev\MapGeometries\MarkerCollection;

$markers = MarkerCollection::make([
    Marker::make(40.416775, -3.703790)->label('Madrid'),
    Marker::make(41.385064, 2.173404)->label('Barcelona'),
    Marker::make(39.469907, -0.376288)->label('Valencia'),
]);
@endphp

<livewire:livewire-map :markers="$markers" />
```

### Modo de Solo Lectura

```blade
<livewire:livewire-map
    :latitude="40.416775"
    :longitude="-3.703790"
    :interactive="false"
/>
```

### Con Todas las Opciones

```blade
<livewire:livewire-map
    :marker="$marker"
    :interactive="true"
    :showLabel="true"
    :showPasteButton="true"
    :height="500"
    :zoom="15"
/>
```

## Propiedades

### Latitude

- **Tipo:** `?float`
- **Default:** `null`
- **Descripción:** Latitud inicial (modo legacy)

### Longitude

- **Tipo:** `?float`
- **Default:** `null`
- **Descripción:** Longitud inicial (modo legacy)

### Marker

- **Tipo:** `?Marker`
- **Default:** `null`
- **Descripción:** Marcador individual (modo geometrías)

### Markers

- **Tipo:** `?MarkerCollection`
- **Default:** `null`
- **Descripción:** Colección de marcadores (modo multi-marker)

### Interactive

- **Tipo:** `?bool`
- **Default:** `true`
- **Descripción:** Permite interacción con el mapa

### ShowLabel

- **Tipo:** `?bool`
- **Default:** `true`
- **Descripción:** Muestra etiqueta con coordenadas

### ShowPasteButton

- **Tipo:** `?bool`
- **Default:** `false`
- **Descripción:** Muestra botón para pegar coordenadas

### Height

- **Tipo:** `?int`
- **Default:** `400`
- **Descripción:** Altura del mapa en píxeles

### Zoom

- **Tipo:** `?int`
- **Default:** `15`
- **Descripción:** Nivel de zoom inicial

> **Nota:** Todos los valores por defecto se pueden configurar en `config/livewire-maps.php`

## Configuración

El archivo de configuración `config/livewire-maps.php` permite personalizar los valores por defecto:

```php
return [
    // Coordenadas por defecto cuando no se especifican
    'default_latitude' => 36.9990019,
    'default_longitude' => -6.5478919,
    'default_zoom' => 15,
    'default_height' => 400,

    // Configuración del tile layer (OpenStreetMap por defecto)
    'tile_layer' => [
        'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'attribution' => '© OpenStreetMap contributors',
        'max_zoom' => 19,
    ],

    // Comportamiento por defecto de los componentes
    'interactive' => true,
    'show_label' => true,
    'show_paste_button' => false,
];
```

## Integración con Formularios

### Usando en un formulario Livewire

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use LBCDev\MapGeometries\Marker;

class CreateLocation extends Component
{
    public $name;
    public $latitude;
    public $longitude;
    public ?Marker $marker = null;

    protected $listeners = ['map-coordinates-updated' => 'updateMapCoordinates'];

    public function updateMapCoordinates($data)
    {
        $this->latitude = $data['latitude'];
        $this->longitude = $data['longitude'];

        // Actualizar el marker
        $this->marker = Marker::make($this->latitude, $this->longitude);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Guardar en la base de datos...
    }

    public function render()
    {
        return view('livewire.create-location');
    }
}
```

### Vista del formulario

```blade
<div>
    <form wire:submit="save">
        <div class="mb-4">
            <label class="block mb-2">Nombre</label>
            <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded">
            @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-2">Ubicación en el mapa</label>
            <livewire:livewire-map
                :latitude="$latitude"
                :longitude="$longitude"
                :showPasteButton="true"
            />
            @error('latitude') <span class="text-red-500">{{ $message }}</span> @enderror
            @error('longitude') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">
            Guardar
        </button>
    </form>
</div>
```

## API del Componente

### Métodos Públicos

```php
// Verificar si está en modo multi-marker
$component->isMultiMarkerMode(): bool

// Verificar si tiene un marcador único
$component->hasSingleMarker(): bool

// Obtener el marcador actual
$component->getMarker(): ?Marker

// Obtener la colección de marcadores
$component->getMarkers(): ?MarkerCollection

// Añadir un marcador (cambia a modo multi-marker)
$component->addMarker(Marker $marker): void

// Eliminar un marcador por índice
$component->removeMarker(int $index): void

// Limpiar todos los marcadores
$component->clearMarkers(): void

// Actualizar coordenadas (solo en modo interactivo)
$component->updateCoordinates(float $lat, float $lng): void
```

### Propiedades Computadas

```php
// Latitud a mostrar (usa default si es null)
$component->displayLatitude: float

// Longitud a mostrar (usa default si es null)
$component->displayLongitude: float

// Verifica si tiene coordenadas válidas
$component->hasCoordinates: bool

// Datos de markers para renderizar
$component->markersData: array
```

## Eventos

### Eventos que emite el componente

#### `map-coordinates-updated`

Se emite cuando las coordenadas cambian (click en mapa, arrastrar marcador, o entrada manual):

```php
$this->dispatch('map-coordinates-updated', [
    'latitude' => 40.416775,
    'longitude' => -3.703790
]);
```

### Eventos que escucha el componente

#### `fly-to-coordinates`

Anima el mapa hacia unas coordenadas específicas:

```php
$this->dispatch('fly-to-coordinates', [
    'latitude' => 40.416775,
    'longitude' => -3.703790
]);
```

## Personalización

### Estilos personalizados

El componente utiliza clases de Tailwind CSS. Puedes personalizar los estilos publicando las vistas y modificándolas según tus necesidades.

### Coordenadas por defecto

Las coordenadas por defecto se configuran en `config/livewire-maps.php`:

```php
'default_latitude' => 36.9990019,
'default_longitude' => -6.5478919,
```

## Ejemplos Avanzados

### Trabajando con Markers Personalizados

```blade
@php
use LBCDev\MapGeometries\Marker;

$marker = Marker::make(40.416775, -3.703790)
    ->label('Oficina Central')
    ->tooltip('Haz clic para más información')
    ->icon('custom-icon')
    ->iconColor('#FF5733')
    ->metadata(['id' => 1, 'type' => 'office']);
@endphp

<livewire:livewire-map :marker="$marker" />
```

### Mapa con Múltiples Ubicaciones

```blade
@php
use LBCDev\MapGeometries\Marker;
use LBCDev\MapGeometries\MarkerCollection;

$offices = MarkerCollection::make([
    Marker::make(40.416775, -3.703790)->label('Madrid')->tooltip('Oficina Principal'),
    Marker::make(41.385064, 2.173404)->label('Barcelona')->tooltip('Oficina Norte'),
    Marker::make(39.469907, -0.376288)->label('Valencia')->tooltip('Oficina Este'),
]);
@endphp

<livewire:livewire-map :markers="$offices" :height="600" />
```

### Selector de Ubicación para Direcciones

```blade
<div>
    <div class="mb-4">
        <input
            type="text"
            placeholder="Buscar dirección..."
            wire:model.live="searchAddress"
            class="w-full px-3 py-2 border rounded"
        >
    </div>

    <livewire:livewire-map
        :latitude="$latitude"
        :longitude="$longitude"
        :showPasteButton="true"
        :height="500"
    />
</div>
```

### Múltiples Mapas en una Página

```blade
<div class="grid grid-cols-2 gap-4">
    <div>
        <h3 class="mb-2">Ubicación de origen</h3>
        <livewire:livewire-map
            :latitude="$originLat"
            :longitude="$originLng"
            wire:key="origin-map"
        />
    </div>

    <div>
        <h3 class="mb-2">Ubicación de destino</h3>
        <livewire:livewire-map
            :latitude="$destLat"
            :longitude="$destLng"
            wire:key="destination-map"
        />
    </div>
</div>
```

### Añadir Marcadores Dinámicamente

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use LBCDev\MapGeometries\Marker;
use LBCDev\MapGeometries\MarkerCollection;

class DynamicMarkers extends Component
{
    public ?MarkerCollection $markers = null;

    public function mount()
    {
        $this->markers = MarkerCollection::make();
    }

    public function addLocation($lat, $lng, $label)
    {
        $marker = Marker::make($lat, $lng)->label($label);
        $this->markers->add($marker);
    }

    public function render()
    {
        return view('livewire.dynamic-markers');
    }
}
```

## Estructura del Paquete

```shell
packages/core/
├── config/
│   └── livewire-maps.php          # Configuración del paquete
├── resources/
│   └── views/
│       └── livewire-map.blade.php # Vista del componente
├── src/
│   ├── Components/
│   │   └── LivewireMap.php        # Componente principal
│   └── LivewireMapsServiceProvider.php
└── tests/
    ├── Feature/
    └── Unit/
        └── LivewireMapWithGeometriesTest.php
```

## Namespace y Clases

- **Namespace principal:** `LBCDev\LivewireMaps`
- **Componente:** `LBCDev\LivewireMaps\Components\LivewireMap`
- **ServiceProvider:** `LBCDev\LivewireMaps\LivewireMapsServiceProvider`
- **Namespace de vistas:** `livewire-maps`

## Testing

El paquete incluye tests completos:

```bash
cd packages/core
composer test
```

## Compatibilidad

### Modo Legacy

El componente mantiene retrocompatibilidad con el uso de `latitude` y `longitude` directos:

```blade
<livewire:livewire-map :latitude="40.416775" :longitude="-3.703790" />
```

### Modo Geometrías (Recomendado)

Usa objetos `Marker` y `MarkerCollection` para mayor flexibilidad:

```blade
<livewire:livewire-map :marker="$marker" />
```

## Soporte

Si encuentras algún problema o tienes sugerencias:

- 🐛 [Reportar un bug](https://github.com/Luinux81/livewire-maps-core/issues)
- 💡 [Solicitar una característica](https://github.com/Luinux81/livewire-maps-core/issues)

## Licencia

Este paquete es software de código abierto licenciado bajo la [Licencia MIT](LICENSE).

## Créditos

- Desarrollado por [LBCDev](https://github.com/Luinux81)
- Utiliza [Leaflet.js](https://leafletjs.com/) para los mapas
- Construido con [Livewire](https://livewire.laravel.com/)
- Integración con [lbcdev/map-geometries](https://github.com/Luinux81/map-geometries)
