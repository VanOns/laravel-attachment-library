## Glide

The Laravel Attachment Library uses Glide to generate image URLs. Glide is a powerful image manipulation library that allows you to resize, crop, and apply various effects to images on the fly.

Glide stores the resized images in a cache directory, which is configurable in the `config/glide.php` file. By default, the cache directory is set to `storage/app/img`.
A symbolic link is created to the `public/img` directory, so your webserver can serve the images directly. This drastically improves performance and reduces the load on your server when not using a CDN.

### Fit presets

Preset image URLs have the shape `/img/{preset}/{breakpoint}/{format}/{fit}/{path}`. Because the generated image is cached under that exact path, your webserver can serve it directly on subsequent requests.

The `{fit}` segment must be one of the fit presets configured in `config/glide.php`. Requests using an unknown fit are rejected with a `403`:

```php
'fit_presets' => [
    'crop' => \VanOns\LaravelAttachmentLibrary\Enums\Fit::CROP->value,
    'contain' => \VanOns\LaravelAttachmentLibrary\Enums\Fit::CONTAIN->value,
],
```

The keys are used in the URL, so they cannot contain slashes or special characters. The values must be a value of the `Fit` enum.

When the attachment has a focal point, it takes priority over the requested preset, but only when that preset resolves to `crop`: the image is then rendered as `crop-{x}-{y}`. Fits for which a focal point has no meaning, such as `contain`, are used as requested. Note that the image is always cached under the fit that was requested, not the one it was rendered with, so that the URL keeps matching the cached file.

To see the amount of space used by the Glide cache, you can run the following command:
```bash
php artisan glide:stats
```

To clear the Glide cache, you can run the following command:
```bash
php artisan glide:clear
```
