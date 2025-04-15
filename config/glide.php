<?php

return [

    'driver' => env('GLIDE_DRIVER', 'gd'),
    'source' => storage_path('app/public'),
    /**
     * The disk that will be used to store the resized images.
     * Can be a name of an existing disk like public or a disk configuration.
     * See: https://laravel.com/docs/9.x/filesystem#configuration
     */
    'cache_disk' => [
        'driver' => 'local',
        'root' => storage_path('app/img'),
        'url' => env('APP_URL') . '/img',
        'visibility' => 'public',
    ],
    /**
     * Here you can configure additional symbolic links that will
     * be created when the `storage:link` command is run.
     */
    'links' => [ public_path('img') => storage_path('app/img') ],
    'defaults' => [],
    'presets' => [],
    'max_image_size' => 2160 * 2160,

    /**
     * The breakpoints that are used in the application.
     */
    'breakpoints' => [
        'xxs' => 320,
        'xs' => 375,
        'sm' => 480,
        'md' => 786,
        'lg' => 1024,
        'xl' => 1440,
    ],

    /**
     * The available image size ratios.
     * A value of 1 means that the image will be exactly the size as the breakpoint width.
     * While a value of 0.5 means that the image will be half the size of the breakpoint width.
     */
    'sizes' => [
        'huge' => 1.5,
        'full' => 1,
        'large' => 0.75,
        'medium' => 0.50,
        'small' => 0.25,
    ],

    /**
     * The image formats that will be used.
     * Formats should be put in order from best to worst, browsers will attempt to load the formats in order
     * Example: With [ 'webp' , 'jpg' ] browsers will attempt to load webp before jpg.
     */
    'formats' => ['webp', 'jpg'],

];
