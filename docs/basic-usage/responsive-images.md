# Responsive images

This package includes a Blade component for loading responsive images that scale depending on breakpoints. This ensures
optimal performance by delivering appropriately sized images for different screen sizes.

## Usage

To use the responsive image component, simply include it in your Blade template as follows:

```php
<x-laravel-attachment-library-image :src="$image" />
```

## Parameters

- `src`: The source of the image. This can be a file path string, an Attachment object, or a numeric ID.
- `size`: The size ratio of the image. Available sizes are defined in the `glide.php` config file.
- `aspectRatio`: The aspect ratio of the image.
- `class`: Additional CSS-classes.

## Manually resize image

The `Resizer` class is responsible for resizing images. It is also possible to call this class directly from other places
in the application.

The `Resizer` class returns an array with width, height and url. The URL should be used as source for the image element.
An example code snippet could be:

```php
\VanOns\LaravelAttachmentLibrary\Facades\Resizer::src($image)
    ->height(200)
    ->width(500)
    ->resize();

/**
 * Valid source:
 * [
 *   'width' => 500,
 *   'height' => 200,
 *   'url' => 'http://test.local/img/path-to-image.jpg?w=500&h=200&signature=....'
 * ]
 * 
 * Invalid source:
 * []
 */
```
