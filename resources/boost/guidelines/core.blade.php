## Laravel Attachment Library (`van-ons/laravel-attachment-library`)

Keeps files on a Laravel disk with one `attachments` row per file, links them to any Eloquent model through the polymorphic `attachables` pivot (with a `collection` and an `order`), resizes images on the fly with Glide through signed URLs, renders responsive `<picture>` and `<video>` Blade components, and stores video dimensions, posters and caption tracks with ffmpeg. `LaravelAttachmentLibraryServiceProvider` and `GlideServiceProvider` are auto-discovered. For a Filament media field and file manager, use `van-ons/filament-attachment-library`, which builds on this package.

### Setup

@verbatim
<code-snippet name="Install" lang="bash">
composer require van-ons/laravel-attachment-library
php artisan attachment-library:install   # publishes config + migrations, asks to migrate

# Non-interactive equivalent
php artisan vendor:publish --tag=attachment-library-config --tag=attachment-library-migrations
php artisan migrate

php artisan storage:link   # also links public/img to the Glide cache (glide.links)
</code-snippet>
@endverbatim

- Publish tags: `attachment-library-config` (both `config/attachment-library.php` and `config/glide.php`), `attachment-library-migrations`, `laravel-attachment-library-views`.
- The migrations ship as `.php.stub` files and only run once published. After an update that adds one, publish the tag again: migrations the app already has are matched by name and kept.
- Files go on `ATTACHMENTS_DISK` (default `public`). Use a disk with no other files on it, since everything on it shows up in the library.

### Public API

@verbatim
| Class | Purpose |
|---|---|
| `Concerns\HasAttachments` | Model trait. `attachments(): MorphToMany` (ordered by pivot `order`), `attachmentCollection(string $collection)`. |
| `Models\Attachment` | `name`, `extension`, `path` (directory, `null` = disk root), `disk`, `mime_type`, `size`, `title`, `alt`, `caption`, `description`, `focal_point` (`{x, y}` in %), `width`, `height`, `duration`, `poster_id`. Accessors: `url`, `filename`, `full_path`, `absolute_path`, `type`, `aspect_ratio`, `metadata`. `isImage()`, `isVideo()`, `isAudio()`, `isDocument()`, `fileExists()`, `getContents()`, `poster()`, `captions()`, `related($class)`, `Attachment::findOrdered(array $ids)`. |
| `AttachmentQueryBuilder` | Builder on `Attachment`: `whereDisk()`, `wherePath(?string)`, `whereInPath(string)` (includes subdirectories), `whereFilename(Filename)`, `whereType(AttachmentType::*)`. |
| `Facades\AttachmentManager` | `upload(UploadedFile, ?string $path)`, `replace(UploadedFile, Attachment)`, `rename(Attachment, string $name)`, `move(Attachment, ?string $path)`, `delete(Attachment)`, `createDirectory(string $path, DirectoryStrategies ...)`, `renameDirectory()`, `deleteDirectory()`, `directories(?string)`, `files(?string)`, `file(string $path)`, `find($id)`, `findByUrl(string)`, `updateFiles(?string)`, `setDisk(string)`, `processVideo()`, `generatePoster()`, `syncCaptions()`. |
| `Facades\Resizer` | Fluent Glide URL builder, a fresh instance per call: `src(Attachment\|int\|string)`, `width()`, `height()`, `size()`, `aspectRatio('16/9'\|float)`, `fit(Fit)`, `crop($x, $y, $zoom)`, `format('webp')`, `resize()` → `['width', 'height', 'url']`, or `[]` when Glide cannot handle the file. |
| `Facades\Glide` | `imageIsSupported($path)`, `cacheStats()`, `server()`. |
| `Enums\AttachmentType` | String constants, not an enum: `PREVIEWABLE_IMAGE`, `PREVIEWABLE_VIDEO`, `PREVIEWABLE_AUDIO`, `PREVIEWABLE_DOCUMENT`, mapped from MIME types in `attachment_mime_type_mapping`. |
| `Enums\Fit` | `CONTAIN`, `CROP`, `FILL`, `FILL_MAX`, `MAX`, `STRETCH`. |
| `Http\Resources\AttachmentResource` | JSON resource; includes `poster` and `captions` when eager loaded. |
| `<x-laravel-attachment-library-image>` | Responsive `<picture>` with a source per breakpoint and format. `src` (attachment, id or path), `size` (a key of `glide.sizes`), `aspect-ratio`, `class`, `image-class`, `alt` (falls back to the attachment's), `fit` (`contain`/`cover`, CSS only), `loading`. |
| `<x-laravel-attachment-library-video>` | `<video>` with intrinsic size, resized poster and caption tracks. Extra attributes go on the element; `controls` and `preload="metadata"` by default. |
@endverbatim

Artisan: `attachment-library:install`, `attachment-library:process-videos {--posters} {--force}`, `glide:clear`, `glide:stats`.

### Config

`config/attachment-library.php`: `disk`, `serve_attachments_from_disk` (env `ATTACHMENTS_SERVE_FROM_DISK`), `auto_sync`, `auto_sync_interval`, `metadata_retrievers`, `ffmpeg.ffmpeg_path`/`ffprobe_path`/`timeout` (env `FFMPEG_PATH`, `FFPROBE_PATH`), `allowed_characters`, `class_mapping` (`attachment`, `attachment_manager`, `directory`), `attachment_mime_type_mapping`, `file_namers`.

`config/glide.php`: `driver` (env `GLIDE_DRIVER`, `gd`), `origin` (env `GLIDE_ORIGIN`), `cache_disk`, `links`, `presets`, `fit_presets`, `max_image_size`, `breakpoints`, `sizes`, `formats`.

### Usage

@verbatim
<code-snippet name="Attach, upload and render" lang="php">
use VanOns\LaravelAttachmentLibrary\Concerns\HasAttachments;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Facades\Resizer;

class Project extends Model
{
    use HasAttachments;

    public function gallery(): MorphToMany
    {
        return $this->attachmentCollection('gallery');
    }
}

$attachment = AttachmentManager::upload($request->file('photo'), 'projects/2026');
$project->attachments()->attach($attachment, ['collection' => 'gallery', 'order' => 1]);

$image = Resizer::src($attachment)->width(800)->aspectRatio('16/9')->format('webp')->resize();
</code-snippet>

<code-snippet name="Blade components" lang="blade">
<x-laravel-attachment-library-image :src="$project->gallery->first()" size="medium" aspect-ratio="4/3" loading="lazy" />
<x-laravel-attachment-library-video :src="$video" crossorigin="anonymous" class="w-full" />
</code-snippet>
@endverbatim

### Conventions and gotchas

- **Go through `AttachmentManager`** for uploading, renaming, moving and deleting, so the disk and the `attachments` table stay in sync. Paths are relative to the disk root without a leading slash; `rename()` takes the name without its extension.
- `upload()` throws `DestinationAlreadyExistsException` when the file exists, and `DisallowedCharacterException` for a name outside `allowed_characters` or without an extension. `createDirectory()` throws `NoParentDirectoryException` unless the parent exists or `DirectoryStrategies::CREATE_PARENT_DIRECTORIES` is passed. `deleteDirectory()` removes every file and row below it.
- Files written to the disk some other way get rows when `directories()` syncs that directory (with `auto_sync`, at most once per `auto_sync_interval` seconds) or after `AttachmentManager::updateFiles($directory)`.
- `$attachment->url` is the `/files/{path}` route (named `attachment`, `web` middleware only, no authorization) unless `serve_attachments_from_disk` is on, then the disk's own URL.
- **Never build Glide URLs by hand.** `/img/{options}/{path}` (route `glide`) checks a signature, so use `Resizer` or the image component. Preset URLs `/img/{preset}/{breakpoint}/{format}/{fit}/{path}` (route `glide.preset`) are unsigned but only accept configured values (`breakpoint` is the pixel width) and answer `403` otherwise; they are cached under that exact path so the webserver can serve them.
- A focal point crops images when the `Resizer` gets an `Attachment` (not a path) and the fit is `crop`.
- When extending the model, `class_mapping.attachment` is what `AttachmentManager`, `HasAttachments` and the observer use, but the components and `Resizer::src($id)` query the package `Attachment` directly.
- The facade keeps one `AttachmentManager` instance, so `setDisk()` sticks for every later facade call; set it back when done.
- Video dimensions, duration and posters need `ffmpeg` and `ffprobe`; without them videos upload as usual and those steps are skipped. `syncCaptions()` converts a SubRip (`.srt`) caption to a WebVTT file next to it and links that instead. Run `attachment-library:process-videos --posters` for videos uploaded before ffmpeg was available.
- `created_by` and `updated_by` are filled from the authenticated user by an observer.
