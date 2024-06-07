<?php

return [
    'driver' => env('GLIDE_DRIVER', 'gd'),
    'source' => storage_path('app/media'),
    'cache' => storage_path('app/glide/cache'),
    'defaults' => [],
    'presets' => [],
    'max_image_size' => 2160 * 2160,
];
