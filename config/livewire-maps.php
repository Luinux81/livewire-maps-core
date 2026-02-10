<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Map Configuration
    |--------------------------------------------------------------------------
    |
    | These are the default settings for the Livewire Maps component.
    | You can override these values when instantiating the component.
    |
    */

    'default_latitude' => 36.9990019,
    'default_longitude' => -6.5478919,
    'default_zoom' => 15,
    'default_height' => 400,

    /*
    |--------------------------------------------------------------------------
    | Tile Layer Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the tile layer provider for the maps.
    | Default is OpenStreetMap.
    |
    */

    'tile_layer' => [
        'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'attribution' => '© OpenStreetMap contributors',
        'max_zoom' => 19,
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Defaults
    |--------------------------------------------------------------------------
    |
    | Default behavior for map components.
    |
    */

    'interactive' => true,
    'show_label' => true,
    'show_paste_button' => false,
];

