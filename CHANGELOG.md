# Changelog

All changes to this project will be documented in this file.

## Unreleased

### Added

- Video dimensions and duration, stored on upload when `ffmpeg` and `ffprobe` are available.
- Video poster images, generated from the first frame on upload.
- Caption tracks for videos, with SubRip (`.srt`) files converted to WebVTT.
- `<x-laravel-attachment-library-video>` Blade component.
- `attachment-library:process-videos` command for existing videos.
- `whereType()` query scope.
