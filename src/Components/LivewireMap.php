<?php

namespace LBCDev\LivewireMaps\Components;

use Illuminate\Support\Facades\View;
use LBCDev\MapGeometries\Marker;
use LBCDev\MapGeometries\MarkerCollection;
use Livewire\Component;
use Livewire\Attributes\Computed;

class LivewireMap extends Component
{
    // Propiedades públicas (legacy - mantener por compatibilidad)
    public ?float $latitude = null;
    public ?float $longitude = null;

    // Geometrías PÚBLICAS (serializables por Livewire por Wireable)
    public ?Marker $marker = null;
    public ?MarkerCollection $markers = null;

    // Propiedades de configuración
    public ?bool $interactive = null;
    public ?bool $showLabel = null;
    public ?bool $showPasteButton = null;
    public ?int $height = null;
    public ?int $zoom = null;
    public bool $showCoordinateInput = false;
    public string $coordinateInput = '';

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function mount(
        $latitude = null,
        $longitude = null,
        // ?Marker $marker = null,
        // ?MarkerCollection $markers = null,
        $marker = null,
        $markers = null,
        ?bool $interactive = null,
        ?bool $showLabel = null,
        ?bool $showPasteButton = null,
        ?int $height = null,
        ?int $zoom = null
    ): void {
        // Prioridad: markers > marker > latitude/longitude (legacy)
        if ($markers?->count() > 0) {
            $this->markers = $markers;
        } elseif ($marker !== null) {
            $this->marker = $marker;
            $this->latitude = $marker->getLatitude();
            $this->longitude = $marker->getLongitude();
        } else {
            $this->latitude = is_string($latitude) ? (float) $latitude : $latitude;
            $this->longitude = is_string($longitude) ? (float) $longitude : $longitude;

            if ($this->latitude !== null && $this->longitude !== null) {
                $this->marker = Marker::make($this->latitude, $this->longitude);
            }
        }

        $this->interactive = $interactive ?? config('livewire-maps.interactive', true);
        $this->showLabel = $showLabel ?? config('livewire-maps.show_label', true);
        $this->showPasteButton = $showPasteButton ?? config('livewire-maps.show_paste_button', false);
        $this->height = $height ?? config('livewire-maps.default_height', 400);
        $this->zoom = $zoom ?? config('livewire-maps.default_zoom', 15);
    }

    public function updateCoordinates(float $lat, float $lng): void
    {
        if (! $this->interactive) {
            return;
        }

        $this->latitude = round($lat, 6);
        $this->longitude = round($lng, 6);

        if ($this->marker !== null) {
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

        $this->dispatch('fly-to-coordinates', [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);
    }

    // PROPIEDADES COMPUTADAS
    #[Computed]
    public function displayLatitude(): float
    {
        return $this->latitude ?? (float) config('livewire-maps.default_latitude', 36.9990019);
    }

    #[Computed]
    public function displayLongitude(): float
    {
        return $this->longitude ?? (float) config('livewire-maps.default_longitude', -6.5478919);
    }

    #[Computed]
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    #[Computed]
    public function markersData(): array
    {
        if ($this->isMultiMarkerMode()) {
            return $this->markers->render();
        }

        if ($this->hasSingleMarker()) {
            return [$this->marker->render()];
        }

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

    // MÉTODOS PÚBLICOS
    public function isMultiMarkerMode(): bool
    {
        return $this->markers !== null && ! $this->markers->isEmpty();
    }

    public function hasSingleMarker(): bool
    {
        return $this->marker !== null;
    }

    public function getMarker(): ?Marker
    {
        return $this->marker;
    }

    public function getMarkers(): ?MarkerCollection
    {
        return $this->markers;
    }

    public function addMarker(Marker $marker): void
    {
        if ($this->markers === null) {
            $this->markers = MarkerCollection::make();

            if ($this->marker !== null) {
                $this->markers->add($this->marker);
                $this->marker = null;
            }
        }

        $this->markers->add($marker);
    }

    public function removeMarker(int $index): void
    {
        if ($this->markers !== null) {
            $this->markers->remove($index);
        }
    }

    public function clearMarkers(): void
    {
        $this->markers = null;
        $this->marker = null;
        $this->latitude = null;
        $this->longitude = null;
    }

    public function render()
    {
        return View::make('livewire-maps::livewire-map');
    }
}
