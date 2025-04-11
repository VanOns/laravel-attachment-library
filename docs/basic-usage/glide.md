## Glide

The Laravel Attachment Library uses Glide to generate image URLs. Glide is a powerful image manipulation library that allows you to resize, crop, and apply various effects to images on the fly.

Glide stores the resized images in a cache directory, which is configurable in the `config/glide.php` file. By default, the cache directory is set to `storage/app/img`.
A symbolic link is created to the `public/img` directory, so your webserver can serve the images directly. This drastically improves performance and reduces the load on your server when not using a CDN.

To see the amount of space used by the Glide cache, you can run the following command:
```bash
php artisan glide:stats
```

To clear the Glide cache, you can run the following command:
```bash
php artisan glide:clear
```
