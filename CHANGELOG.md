# Changelog

All notable changes to `livewire-maps-core` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-02-16

### Added

- Initial stable release
- `LivewireMap` Livewire component for interactive maps
- Full integration with Leaflet.js via Alpine.js
- Single marker mode support
- Multi-marker mode with `MarkerCollection` support
- Legacy mode for backward compatibility with separate lat/lng properties
- Coordinate validation (latitude: -90 to 90, longitude: -180 to 180)
- Livewire events for map interaction:
  - `map-coordinates-updated` - Emitted when coordinates change
  - `fly-to-coordinates` - Centers map to specific coordinates
- Configuration system via `config/livewire-maps.php`:
  - Default center coordinates (configurable via env)
  - Default zoom level
  - Tile layer configuration (OpenStreetMap)
  - Default map behavior settings
- Interactive and read-only modes
- Alpine.js integration for reactive map updates
- Comprehensive test suite (13 tests)
- Complete PHPDoc documentation
- Extensive README with examples (525 lines)

### Component Features

- `addMarker()` - Add markers dynamically
- `removeMarker()` - Remove markers by ID
- `clearMarkers()` - Clear all markers
- `flyTo()` - Programmatically center map
- Computed properties:
  - `markersData` - Get all markers as array
  - `hasCoordinates` - Check if valid coordinates are set

### View Features

- Responsive map container
- Custom height support
- Interactive click to set coordinates
- Marker clustering support
- Custom tile layers
- Map options customization (zoom controls, scroll wheel, etc.)

### Configuration

- Environment-based configuration
- Customizable default coordinates
- Configurable zoom levels
- Tile layer customization
- Map behavior defaults

### Testing

- Mount tests (single marker, multi-marker, legacy mode)
- Collection management tests
- Input validation tests
- Event emission tests
- Coordinate validation tests

[Unreleased]: https://github.com/Luinux81/livewire-maps-core/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/Luinux81/livewire-maps-core/releases/tag/v1.0.0
