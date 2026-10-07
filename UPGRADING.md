# Upgrading

We aim to make upgrading between versions as smooth as possible, but sometimes it involves specific steps to be taken.
This document will outline those steps. And as much as we try to cover all cases, we might miss some. If you come
across such a case, please let us know by [opening an issue][issues], or by adding it yourself and creating a pull request.

<!-- EXAMPLE -->
<!--
# v1 to v2

* Remove the `foo` column from the `bar` table.
* Add the `baz` column to the `bar` table.
* Run `php artisan migrate` to update the database.
-->

# v1.6 to v1.7

* Publish and run the new migrations: `php artisan vendor:publish --tag=laravel-attachment-library-migrations` and
  `php artisan migrate`. They add `width`, `height`, `duration` and `poster_id` to `attachments` and create the
  `attachment_captions` table.
* If you published the configuration, add the `ffmpeg` block and the `Video` metadata retriever from the package's
  `config/attachment-library.php`.
* Optionally run `php artisan attachment-library:process-videos --posters` to process existing videos.

[issues]: https://github.com/VanOns/laravel-attachment-library/issues