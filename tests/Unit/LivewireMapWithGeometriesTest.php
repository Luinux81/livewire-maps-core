<?php

namespace LBCDev\LivewireMaps\Tests\Unit;

use Livewire\Livewire;
use LBCDev\MapGeometries\Marker;
use LBCDev\LivewireMaps\Tests\TestCase;
use LBCDev\MapGeometries\MarkerCollection;
use LBCDev\LivewireMaps\Components\LivewireMap;

class LivewireMapWithGeometriesTest extends TestCase
{
    public function test_can_mount_with_single_marker(): void
    {
        $marker = Marker::make(40.7128, -74.0060, 'New York');

        $component = Livewire::test(LivewireMap::class, [
            'marker' => $marker,
        ]);

        // Verificar usando el getter público
        $this->assertEquals(40.7128, $component->get('latitude'));
        $this->assertEquals(-74.0060, $component->get('longitude'));
        $this->assertNotNull($component->instance()->getMarker());
    }

    public function test_can_mount_with_marker_collection(): void
    {
        $markers = MarkerCollection::make([
            Marker::make(40.7128, -74.0060, 'New York'),
            Marker::make(51.5074, -0.1278, 'London'),
        ]);

        /** @var LivewireMap */
        $component = Livewire::test(LivewireMap::class, [
            'markers' => $markers,
        ]);

        $component->assertSet('markers', $markers);
        // $this->assertEquals($markers, $component->instance()->getMarkers());
        $this->assertTrue($component->instance()->isMultiMarkerMode());
    }

    public function test_legacy_mode_creates_marker_automatically(): void
    {
        $component = Livewire::test(LivewireMap::class, [
            'latitude' => 48.8566,
            'longitude' => 2.3522,
        ]);

        $component->assertSet('latitude', 48.8566);
        $component->assertSet('longitude', 2.3522);
        $this->assertInstanceOf(Marker::class, $component->get('marker'));
    }

    public function test_has_single_marker_returns_true_when_marker_exists(): void
    {
        $marker = Marker::make(35.6762, 139.6503, 'Tokyo');

        $component = Livewire::test(LivewireMap::class, [
            'marker' => $marker,
        ]);

        $this->assertTrue($component->instance()->hasSingleMarker());
    }

    public function test_is_multi_marker_mode_returns_true_when_markers_exist(): void
    {
        $markers = MarkerCollection::make([
            Marker::make(1.0, 1.0),
            Marker::make(2.0, 2.0),
        ]);

        $component = Livewire::test(LivewireMap::class, [
            'markers' => $markers,
        ]);

        $this->assertTrue($component->instance()->isMultiMarkerMode());
    }

    public function test_can_add_marker_to_collection(): void
    {
        $component = Livewire::test(LivewireMap::class, [
            'latitude' => 1.0,
            'longitude' => 1.0,
        ]);

        $newMarker = Marker::make(2.0, 2.0, 'Second');
        $component->instance()->addMarker($newMarker);

        $this->assertTrue($component->instance()->isMultiMarkerMode());
        $this->assertEquals(2, $component->get('markers')->count());
    }

    public function test_can_remove_marker_from_collection(): void
    {
        $markers = MarkerCollection::make([
            Marker::make(1.0, 1.0, 'First'),
            Marker::make(2.0, 2.0, 'Second'),
            Marker::make(3.0, 3.0, 'Third'),
        ]);

        $component = Livewire::test(LivewireMap::class, [
            'markers' => $markers,
        ]);

        $component->instance()->removeMarker(1);

        $this->assertEquals(2, $component->get('markers')->count());
    }

    public function test_can_clear_all_markers(): void
    {
        $markers = MarkerCollection::make([
            Marker::make(1.0, 1.0),
            Marker::make(2.0, 2.0),
        ]);

        $component = Livewire::test(LivewireMap::class, [
            'markers' => $markers,
        ]);

        $component->instance()->clearMarkers();

        $this->assertNull($component->get('markers'));
        $this->assertNull($component->get('marker'));
        $this->assertNull($component->get('latitude'));
        $this->assertNull($component->get('longitude'));
    }

    public function test_get_markers_data_returns_correct_structure_for_single_marker(): void
    {
        $marker = Marker::make(40.7128, -74.0060, 'NYC')
            ->tooltip('New York City')
            ->icon('custom-icon');

        $component = Livewire::test(LivewireMap::class, [
            'marker' => $marker,
        ]);

        $data = $component->get('markersData');

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals(40.7128, $data[0]['lat']);
        $this->assertEquals(-74.0060, $data[0]['lng']);
        $this->assertEquals('NYC', $data[0]['label']);
    }

    public function test_get_markers_data_returns_correct_structure_for_multiple_markers(): void
    {
        $markers = MarkerCollection::make([
            Marker::make(1.0, 1.0, 'First'),
            Marker::make(2.0, 2.0, 'Second'),
        ]);

        $component = Livewire::test(LivewireMap::class, [
            'markers' => $markers,
        ]);

        $data = $component->get('markersData');

        $this->assertIsArray($data);
        $this->assertCount(2, $data);
        $this->assertEquals(1.0, $data[0]['lat']);
        $this->assertEquals('First', $data[0]['label']);
        $this->assertEquals(2.0, $data[1]['lat']);
        $this->assertEquals('Second', $data[1]['label']);
    }

    public function test_update_coordinates_updates_marker(): void
    {
        $marker = Marker::make(1.0, 1.0, 'Original');

        $component = Livewire::test(LivewireMap::class, [
            'marker' => $marker,
            'interactive' => true,
        ]);

        $component->call('updateCoordinates', 2.0, 2.0);

        $this->assertEquals(2.0, $component->get('latitude'));
        $this->assertEquals(2.0, $component->get('longitude'));
        $this->assertInstanceOf(Marker::class, $component->get('marker'));
        $this->assertEquals(2.0, $component->get('marker')->getLatitude());
    }

    public function test_it_does_not_update_coordinates_if_not_interactive(): void
    {
        $component = Livewire::test(LivewireMap::class, [
            'latitude' => 1.0,
            'longitude' => 1.0,
            'interactive' => false,
        ]);

        $component->call('updateCoordinates', 5.0, 5.0);

        // Debería mantener las originales
        $this->assertEquals(1.0, $component->get('latitude'));
    }



    // Validación de entradas (Coordenadas inválidas)

    public function test_it_validates_coordinate_input_format(): void
    {
        $component = Livewire::test(LivewireMap::class)
            ->set('coordinateInput', 'invalido')
            ->call('applyCoordinates');

        // Comprobar que el error existe en el campo
        $component->assertHasErrors(['coordinateInput']);

        // Comprobar mensaje (usa un substring para evitar problemas de espacios/puntuación)
        $errors = $component->errors()->get('coordinateInput');
        $this->assertStringContainsString('Formato inválido', $errors[0]);
    }

    public function test_it_validates_coordinate_ranges(): void
    {
        $component = Livewire::test(LivewireMap::class)
            ->set('coordinateInput', '95.0, 200.0') // Latitud > 90, Longitud > 180
            ->call('applyCoordinates');

        $component->assertHasErrors(['coordinateInput' => 'Coordenadas fuera de rango válido']);
    }

    // Emisión de Eventos (JS Interop)

    public function test_it_dispatches_events_on_coordinate_update(): void
    {
        $component = Livewire::test(LivewireMap::class);

        $component->call('updateCoordinates', 10.5, 20.5);

        // Verifica que el JS sepa que tiene que actualizarse
        $component->assertDispatched('map-coordinates-updated', [
            'latitude' => 10.5,
            'longitude' => 20.5,
        ]);
    }

    public function test_it_dispatches_fly_to_when_applying_coordinates(): void
    {
        $component = Livewire::test(LivewireMap::class)
            ->set('coordinateInput', '40.0, -3.0')
            ->call('applyCoordinates');

        $component->assertDispatched('fly-to-coordinates');
    }

    public function test_it_uses_default_values_from_config(): void
    {
        // Simulamos que la config cambia
        config(['livewire-maps.default_zoom' => 10]);
        config(['livewire-maps.default_height' => 600]);

        $component = Livewire::test(LivewireMap::class);

        $component->assertSet('zoom', 10);
        $component->assertSet('height', 600);
    }
}
