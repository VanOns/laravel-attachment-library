# Videos

Videos can have a poster image, known dimensions and caption tracks. Dimensions let the browser reserve space before the
video loads, which prevents layout shifts.

## Requirements

Reading dimensions and generating posters requires the `ffmpeg` and `ffprobe` binaries on the server. Without them,
videos upload as usual and these steps are skipped. Configure the binary paths when they are not on the `PATH`:

```env
FFMPEG_PATH=/usr/bin/ffmpeg
FFPROBE_PATH=/usr/bin/ffprobe
```

## Uploading

When a video is uploaded or replaced, its `width`, `height` and `duration` (in seconds) are stored on the attachment.
If it has no poster yet, its first frame is saved as `<name>-poster.jpg` next to it and linked as its poster. Existing
files are never overwritten: a number is appended to the name instead.

To link another image as the poster, or to generate one on demand:

```php
$video->update(['poster_id' => $image->id]);

\VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager::generatePoster($video);
```

## Captions

Caption tracks are WebVTT (`.vtt`) attachments linked to a video with a language, an optional label and whether the
track is shown by default. SubRip (`.srt`) files are converted to a WebVTT file next to the original, which is linked
instead, because browsers only play WebVTT.

```php
\VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager::syncCaptions($video, [
    ['caption_id' => $dutch->id, 'language' => 'nl', 'label' => 'Nederlands', 'is_default' => true],
    ['caption_id' => $english->id, 'language' => 'en', 'label' => 'English'],
]);

$video->captions; // Ordered, with `language`, `label` and `is_default` on `$caption->pivot`.
```

## Rendering

The video component renders a `<video>` element with its intrinsic size, poster (resized with Glide) and caption tracks:

```php
<x-laravel-attachment-library-video :src="$video" class="w-full" />
```

- `src`: The source of the video. This can be a file path string, an Attachment object, or a numeric ID.
- Other attributes are passed to the `<video>` element. `controls` and `preload="metadata"` are set by default.

The `AttachmentResource` includes `width`, `height`, `duration` and `aspect_ratio`. Eager load `poster` and `captions` to
include them as well:

```php
new AttachmentResource($video->load(['poster', 'captions']));
```

## Existing videos

Store the dimensions of videos uploaded before ffmpeg was available, and optionally generate their posters:

```bash
php artisan attachment-library:process-videos --posters
```

Use `--force` to process every video again.
