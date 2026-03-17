---
name: laravel-attachment-library-development
description: Build and work with the Laravel Attachment Library, including attaching files to Eloquent models, managing directories, responsive images, and Glide-based image resizing.
---

# Laravel Attachment Library Development

## When to use this skill

Use this skill when working with `van-ons/laravel-attachment-library` — for attaching files to Eloquent models, managing upload/move/rename/delete operations via `AttachmentManager`, serving responsive images through Glide, or extending the package with custom adapters, file namers, or model classes.

## Features

- **HasAttachments trait**: Add file attachment support to any Eloquent model.
- **AttachmentManager facade**: Upload, move, rename, and delete attachments and directories while keeping the database in sync.
- **Glide integration**: On-the-fly image resizing with caching; serve responsive images via configurable presets and breakpoints.
- **Responsive image Blade component**: `<x-laravel-attachment-library-image>` renders a `<picture>` element with multiple sources and formats.
- **Resizer facade**: Programmatically generate resized image URLs with explicit width/height.
- **Collections**: Attach files to named collections on a model and query them independently.
- **Ordering**: Attach files with an `order` value; retrieve in a specific order with `Attachment::findOrdered()`.
- **Metadata adapters**: GD and Imagick adapters for reading image metadata (width, height).
- **File namers**: Pipeline of classes that sanitize file names (e.g., `ReplaceControlCharacters`).
- **Extensibility**: Override `Attachment`, `AttachmentManager`, and `Directory` via `class_mapping` in config.

## File Structure

```
config/
  attachment-library.php   # disk, class_mapping, file_namers, allowed_characters, mime types
  glide.php                # driver, presets, breakpoints, sizes, formats, max_image_size
database/
  migrations/              # .php.stub files — published via attachment-library:install
resources/
  views/components/
    image.blade.php        # Responsive <picture> Blade component
src/
  Concerns/HasAttachments.php
  Facades/AttachmentManager.php
  Facades/Glide.php
  Facades/Resizer.php
  Models/Attachment.php
  AttachmentManager.php
  Glide/Resizer.php
```

## Installation

```bash
composer require van-ons/laravel-attachment-library
php artisan attachment-library:install
```

Override the storage disk in `.env` (default is `public`):

```env
ATTACHMENTS_DISK=disk_name_here
```

## Configuring a Model

```php
use Illuminate\Database\Eloquent\Model;
use VanOns\LaravelAttachmentLibrary\Concerns\HasAttachments;

class Post extends Model
{
    use HasAttachments;
}
```

Add a named collection relationship:

```php
use Illuminate\Database\Eloquent\Relations\MorphToMany;

public function gallery(): MorphToMany
{
    return $this->attachmentCollection('gallery');
}
```

## Managing Attachments

```php
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

// Upload (pass an UploadedFile or file path)
$attachment = AttachmentManager::upload($file);

// Move to another directory
AttachmentManager::move($attachment, 'new/path');

// Rename
AttachmentManager::rename($attachment, 'new-name');

// Delete
AttachmentManager::delete($attachment);

// Attach to a model
$post->attachments()->attach($attachment);
$post->attachments()->attach($attachment, ['collection' => 'gallery', 'order' => 1]);

// Detach from a model
$post->attachments()->detach($attachment);

// Retrieve
$post->attachments()->get();
$post->attachments()->wherePivot('collection', 'gallery')->get();

// Retrieve ordered by an array of IDs
$attachments = Attachment::findOrdered([3, 1, 2]);
```

## Managing Directories

```php
// Create
AttachmentManager::createDirectory('path/my-folder');

// Rename
AttachmentManager::renameDirectory('path/my-folder', 'new-name');

// Delete (also removes all files and subdirectories)
AttachmentManager::deleteDirectory('path/my-folder');
```

## Responsive Images (Blade Component)

```blade
<x-laravel-attachment-library-image :src="$attachment" />

{{-- With options --}}
<x-laravel-attachment-library-image
    :src="$attachment"
    size="medium"
    aspectRatio="16/9"
    class="rounded"
    fit="cover"
    alt="Description"
    :lightbox="true"
/>
```

Parameters: `src` (path string, Attachment object, or numeric ID), `size`, `aspectRatio`, `class`, `fit` (contain/cover), `alt`, `lightbox`, `lightboxGallery`.

## Manual Image Resizing (Resizer Facade)

```php
use VanOns\LaravelAttachmentLibrary\Facades\Resizer;

$result = Resizer::src($attachment)->width(800)->height(600)->resize();
// Returns: ['width' => 800, 'height' => 600, 'url' => 'http://...']
// Returns [] for non-image or invalid source
```

## Artisan Commands

- `php artisan attachment-library:install` — Publish config files and migrations, set up the package.
- `php artisan glide:stats` — Show the number of cached images and total cache disk usage.
- `php artisan glide:clear` — Delete all files in the Glide image cache.

## Configuration

```bash
# Publish config and migrations
php artisan attachment-library:install

# Or publish individually
php artisan vendor:publish --tag=attachment-library-config
php artisan vendor:publish --tag=attachment-library-migrations
```

Key `config/attachment-library.php` options:
- `disk` — Storage disk for uploaded files (default: `public`).
- `class_mapping` — Override `attachment`, `attachmentManager`, `directory` with custom classes.
- `file_namers` — Array of file namer classes applied as a pipeline on upload.
- `allowed_characters` — Regex for valid characters in file/directory names.
- `attachment_mime_type_mapping` — Maps `AttachmentType` enum values to supported MIME types.

Key `config/glide.php` options:
- `driver` — Image driver (`gd` or `imagick`).
- `presets` — Named presets with width/height fractions (e.g., `square`, `video`).
- `breakpoints` — Responsive breakpoints in pixels (`sm`, `md`, `lg`, `xl`).
- `sizes` — Size ratios (`huge`, `full`, `large`, `medium`, `small`).
- `formats` — Output format preference order (default: `webp`, `jpg`).
- `max_image_size` — Maximum source dimensions allowed (default: `2160×2160`).

## Extending the Package

Override the `Attachment` model:

```php
// app/Models/ExtendedAttachment.php
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class ExtendedAttachment extends Attachment {}
```

Register the override in `config/attachment-library.php`:

```php
'class_mapping' => [
    'attachment' => \App\Models\ExtendedAttachment::class,
],
```

## Best Practices & Common Pitfalls

- Use a **dedicated storage disk** with no other files to prevent unmanaged files from appearing in the file manager.
- Always use `AttachmentManager` (not direct filesystem calls) to keep the database in sync with stored files.
- `deleteDirectory` removes all contents recursively — ensure the directory is intentionally empty before calling it.
- The Glide cache grows over time; schedule `glide:clear` or monitor with `glide:stats` to manage disk usage.
- `Attachment::findOrdered()` preserves the order of the provided ID array — use it when order comes from user input rather than the `order` pivot column.
- Custom file namers must be registered in the `file_namers` config array to take effect.
