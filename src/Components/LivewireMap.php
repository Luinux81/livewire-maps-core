<?php

namespace LBCDev\LivewireMaps\Components;

use Illuminate\Support\Facades\View;
use LBCDev\MapGeometries\Marker;
use LBCDev\MapGeometries\MarkerCollection;
use Livewire\Component;

class LivewireMap extends Component
{
    // Propiedades públicas (legacy - mantener por compatibilidad)
    public ?float $latitude = null;

    public ?float $longitude = null;

    // Nuevas propiedades para geometries
    public ?Marker $marker = null;

    public ?MarkerCollection $markers = null;

    public bool $interactive = true;

    public bool $showLabel = true;

    public bool $showPasteButton = false;

    public int $height = 400;

    public int $zoom = 15;

    // Propiedades internas
    public bool $showCoordinateInput = false;

    public string $coordinateInput = '';

    // Coordenadas por defecto
    private const DEFAULT_LAT = 36.9990019;

    private const DEFAULT_LNG = -6.5478919;

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Setter para latitude que convierte strings a float
     */
    public function setLatitudeProperty($value): void
    {
        if ($value === null || $value === '') {
            $this->latitude = null;

            return;
        }

        if (is_string($value)) {
            $value = (float) $value;
        }

        $this->latitude = $value;
    }

    /**
     * Getter para latitude
     */
    public function getLatitudeProperty(): ?float
    {
        return $this->latitude;
    }

    /**
     * Setter para longitude que convierte strings a float
     */
    public function setLongitudeProperty($value): void
    {
        if ($value === null || $value === '') {
            $this->longitude = null;

            return;
        }

        if (is_string($value)) {
            $value = (float) $value;
        }

        $this->longitude = $value;
    }

    /**
     * Getter para longitude
     */
    public function getLongitudeProperty(): ?float
    {
        return $this->longitude;
    }

    public function mount(
        $latitude = null,  // Legacy: acepta string o float
        $longitude = null, // Legacy: acepta string o float
        ?Marker $marker = null, // Nuevo: acepta un Marker
        ?MarkerCollection $markers = null, // Nuevo: acepta MarkerCollection
        bool $interactive = true,
        bool $showLabel = true,
        bool $showPasteButton = false,
        int $height = 400,
        int $zoom = 15
    ): void {
        // Prioridad: markers > marker > latitude/longitude (legacy)
        if ($markers !== null) {
            $this->markers = $markers;
        } elseif ($marker !== null) {
            $this->marker = $marker;
            // Sincronizar con propiedades legacy
            $this->latitude = $marker->getLatitude();
            $this->longitude = $marker->getLongitude();
        } else {
            // Modo legacy: usar lat/lng directos
            $this->setLatitudeProperty($latitude);
            $this->setLongitudeProperty($longitude);

            // Si hay coordenadas, crear un marker automáticamente
            if ($this->latitude !== null && $this->longitude !== null) {
                $this->marker = Marker::make($this->latitude, $this->longitude);
            }
        }

        $this->interactive = $interactive;
        $this->showLabel = $showLabel;
        $this->showPasteButton = $showPasteButton;
        $this->height = $height;
        $this->zoom = $zoom;
    }

    public function updateCoordinates(float $lat, float $lng): void
    {
        if (! $this->interactive) {
            return;
        }

        $this->latitude = round($lat, 6);
        $this->longitude = round($lng, 6);

        // Actualizar o crear marker
        if ($this->marker !== null) {
            // Si ya existe un marker, crear uno nuevo con las coordenadas actualizadas
            // (los markers son inmutables por diseño)
            $this->marker = Marker::make($this->latitude, $this->longitude)
                ->label($this->marker->getLabel())
                ->tooltip($this->marker->getTooltip())
                ->icon($this->marker->getIcon())
                ->iconColor($this->marker->getIconColor())
                ->options($this->marker->getOptions())
                ->metadata($this->marker->getMetadata());
        } else {
            $this->marker = Marker::make($this->latitude, $this->longitude);
        }

        // Emitir evento para que el componente padre pueda escuchar
        $this->dispatch('map-coordinates-updated', [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);
    }

    public function toggleCoordinateInput(): void
    {
        $this->showCoordinateInput = ! $this->showCoordinateInput;
    }

    public function applyCoordinates(): void
    {
        if (! $this->interactive) {
            return;
        }

        $parts = array_map('trim', explode(',', $this->coordinateInput));

        if (count($parts) !== 2) {
            $this->addError('coordinateInput', 'Formato inválido. Usa: latitud, longitud');

            return;
        }

        $lat = filter_var($parts[0], FILTER_VALIDATE_FLOAT);
        $lng = filter_var($parts[1], FILTER_VALIDATE_FLOAT);

        if ($lat === false || $lng === false) {
            $this->addError('coordinateInput', 'Las coordenadas deben ser números válidos');

            return;
        }

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            $this->addError('coordinateInput', 'Coordenadas fuera de rango válido');

            return;
        }

        $this->updateCoordinates($lat, $lng);
        $this->showCoordinateInput = false;
        $this->coordinateInput = '';
        $this->resetErrorBag();

        // Emitir evento para que Alpine actualice el mapa
        $this->dispatch('fly-to-coordinates', [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);
    }

    public function getDisplayLatitudeProperty(): float
    {
        return $this->latitude ?? self::DEFAULT_LAT;
    }

    public function getDisplayLongitudeProperty(): float
    {
        return $this->longitude ?? self::DEFAULT_LNG;
    }

    public function getHasCoordinatesProperty(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Check if component is in multi-marker mode
     */
    public function isMultiMarkerMode(): bool
    {
        return $this->markers !== null && ! $this->markers->isEmpty();
    }

    /**
     * Check if component has a single marker
     */
    public function hasSingleMarker(): bool
    {
        return $this->marker !== null;
    }

    /**
     * Get the current marker (single mode)
     */
    public function getMarker(): ?Marker
    {
        return $this->marker;
    }

    /**
     * Get all markers (multi mode)
     */
    public function getMarkers(): ?MarkerCollection
    {
        return $this->markers;
    }

    /**
     * Add a marker to the collection (switches to multi-marker mode)
     */
    public function addMarker(Marker $marker): void
    {
        if ($this->markers === null) {
            $this->markers = MarkerCollection::make();

            // Si había un marker único, añadirlo a la colección
            if ($this->marker !== null) {
                $this->markers->add($this->marker);
                $this->marker = null;
            }
        }

        $this->markers->add($marker);
    }

    /**
     * Remove a marker from the collection by index
     */
    public function removeMarker(int $index): void
    {
        if ($this->markers !== null) {
            $this->markers->remove($index);
        }
    }

    /**
     * Clear all markers
     */
    public function clearMarkers(): void
    {
        $this->markers = null;
        $this->marker = null;
        $this->latitude = null;
        $this->longitude = null;
    }

    /**
     * Get markers data for rendering in the view
     */
    public function getMarkersDataProperty(): array
    {
        if ($this->isMultiMarkerMode()) {
            return $this->markers->render();
        }

        if ($this->hasSingleMarker()) {
            return [$this->marker->render()];
        }

        // Legacy mode: single marker from lat/lng
        if ($this->hasCoordinates) {
            return [[
                'lat' => $this->latitude,
                'lng' => $this->longitude,
                'label' => null,
                'tooltip' => null,
                'icon' => null,
                'iconColor' => null,
                'options' => [],
            ]];
        }

        return [];
    }

    /**
     * Render the component view.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return View::make('livewire-maps::livewire-map');
    }
}
