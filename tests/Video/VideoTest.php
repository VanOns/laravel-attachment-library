<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\Enums\AttachmentType;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Facades\Ffmpeg;
use VanOns\LaravelAttachmentLibrary\Http\Resources\AttachmentResource;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

function uploadVideo(string $name = 'clip.mp4', ?string $path = null): Attachment
{
    return AttachmentManager::upload(UploadedFile::fake()->create($name, 100, 'video/mp4'), $path);
}

function uploadCaption(string $name, string $contents): Attachment
{
    return AttachmentManager::upload(UploadedFile::fake()->createWithContent($name, $contents));
}

beforeEach(function () {
    Storage::fake('test');

    Config::set('attachment-library.disk', 'test');
    Config::set('attachment-library.attachment_mime_type_mapping', [
        AttachmentType::PREVIEWABLE_IMAGE => ['image/jpeg'],
        AttachmentType::PREVIEWABLE_VIDEO => ['video/mp4'],
    ]);

    $this->be(new User());
});

describe('ffmpeg', function () {
    it('is unavailable when a binary cannot be executed', function () {
        fakeMissingFfmpeg();

        expect(Ffmpeg::isAvailable())->toBeFalse();
    });

    it('probes the dimensions and duration of a video', function () {
        fakeMissingFfmpeg();
        $video = uploadVideo();
        fakeFfmpeg(['width' => 1280, 'height' => 720], '3.2');

        expect(Ffmpeg::probe($video))->toBe(['width' => 1280, 'height' => 720, 'duration' => 3.2]);
    });

    it('returns no dimensions when the file has no video stream', function () {
        fakeMissingFfmpeg();
        $video = uploadVideo();
        fakeFfmpeg(null);

        expect(Ffmpeg::probe($video))->toBeNull();
    });

    it('uses the configured binaries', function () {
        Config::set('attachment-library.ffmpeg.ffprobe_path', '/opt/bin/ffprobe');
        fakeMissingFfmpeg();

        Ffmpeg::isAvailable();

        Process::assertRan(fn ($process) => $process->command[0] === 'ffmpeg');
        Process::assertDidntRun(fn ($process) => $process->command[0] === 'ffprobe');
    });
});

describe('uploading', function () {
    it('stores dimensions, duration and a first-frame poster', function () {
        fakeFfmpeg();

        $video = uploadVideo('clip.mp4', 'videos');

        expect($video->width)->toBe(1920)
            ->and($video->height)->toBe(1080)
            ->and($video->duration)->toBe(12.5)
            ->and($video->aspect_ratio)->toBe(1920 / 1080)
            ->and($video->poster->full_path)->toBe('videos/clip-poster.jpg')
            ->and($video->poster->mime_type)->toBe('image/jpeg');

        Storage::disk('test')->assertExists('videos/clip-poster.jpg');
    });

    it('does not overwrite an existing file when generating a poster', function () {
        fakeFfmpeg();
        Storage::disk('test')->put('clip-poster.jpg', 'existing');

        $video = uploadVideo();

        expect($video->poster->filename)->toBe('clip-poster-1.jpg')
            ->and(Storage::disk('test')->get('clip-poster.jpg'))->toBe('existing');
    });

    it('uploads a video without processing when ffmpeg is unavailable', function () {
        fakeMissingFfmpeg();

        $video = uploadVideo();

        expect($video->exists)->toBeTrue()
            ->and($video->width)->toBeNull()
            ->and($video->poster_id)->toBeNull()
            ->and(Attachment::count())->toBe(1);
    });

    it('does not generate a poster when the file has no video stream', function () {
        fakeFfmpeg(null);

        $video = uploadVideo();

        expect($video->width)->toBeNull()
            ->and($video->poster_id)->toBeNull();
    });

    it('does not process images', function () {
        fakeFfmpeg();

        AttachmentManager::upload(UploadedFile::fake()->image('photo.jpg'));

        Process::assertNothingRan();
    });

    it('keeps the existing poster when replacing a video', function () {
        fakeFfmpeg();
        $video = uploadVideo();
        $posterId = $video->poster_id;

        fakeFfmpeg(['width' => 640, 'height' => 480]);
        AttachmentManager::replace(UploadedFile::fake()->create('clip.mp4', 200, 'video/mp4'), $video);

        expect($video->fresh()->poster_id)->toBe($posterId)
            ->and($video->fresh()->width)->toBe(640)
            ->and(Attachment::count())->toBe(2);
    });

    it('unlinks the poster when it is deleted', function () {
        fakeFfmpeg();
        $video = uploadVideo();

        AttachmentManager::delete($video->poster);

        expect($video->fresh()->poster_id)->toBeNull();
    });
});

describe('captions', function () {
    beforeEach(function () {
        fakeMissingFfmpeg();
        $this->video = uploadVideo();
    });

    it('links webvtt tracks in order', function () {
        $english = uploadCaption('en.vtt', "WEBVTT\n");
        $dutch = uploadCaption('nl.vtt', "WEBVTT\n");

        AttachmentManager::syncCaptions($this->video, [
            ['caption_id' => $dutch->id, 'language' => 'nl', 'label' => 'Nederlands', 'is_default' => true],
            ['caption_id' => $english->id, 'language' => 'en'],
        ]);

        $captions = $this->video->captions;

        expect($captions->pluck('id')->all())->toBe([$dutch->id, $english->id])
            ->and($captions[0]->pivot->language)->toBe('nl')
            ->and($captions[0]->pivot->label)->toBe('Nederlands')
            ->and($captions[0]->pivot->is_default)->toBeTrue()
            ->and($captions[1]->pivot->label)->toBeNull()
            ->and($captions[1]->pivot->is_default)->toBeFalse();
    });

    it('converts subrip files to a webvtt file next to them', function () {
        $srt = uploadCaption('en.srt', "1\n00:00:01,000 --> 00:00:02,000\nHello\n");

        AttachmentManager::syncCaptions($this->video, [['caption_id' => $srt->id, 'language' => 'en']]);

        $caption = $this->video->captions->sole();

        expect($caption->filename)->toBe('en.vtt')
            ->and($caption->getContents())->toStartWith("WEBVTT\n\n1\n00:00:01.000")
            ->and(Storage::disk('test')->exists('en.srt'))->toBeTrue();
    });

    it('reuses an unchanged conversion when saving again', function () {
        $srt = uploadCaption('en.srt', "1\n00:00:01,000 --> 00:00:02,000\nHello\n");

        AttachmentManager::syncCaptions($this->video, [['caption_id' => $srt->id, 'language' => 'en']]);
        AttachmentManager::syncCaptions($this->video, [['caption_id' => $srt->id, 'language' => 'en']]);

        expect(Attachment::where('extension', 'vtt')->count())->toBe(1);
    });

    it('does not overwrite an existing webvtt file with a different content', function () {
        uploadCaption('en.vtt', "WEBVTT\n\nhand-written\n");
        $srt = uploadCaption('en.srt', "1\n00:00:01,000 --> 00:00:02,000\nHello\n");

        AttachmentManager::syncCaptions($this->video, [['caption_id' => $srt->id, 'language' => 'en']]);

        expect($this->video->captions->sole()->filename)->toBe('en-1.vtt')
            ->and(Storage::disk('test')->get('en.vtt'))->toContain('hand-written');
    });

    it('removes tracks that are no longer given', function () {
        $caption = uploadCaption('en.vtt', "WEBVTT\n");
        AttachmentManager::syncCaptions($this->video, [['caption_id' => $caption->id, 'language' => 'en']]);

        AttachmentManager::syncCaptions($this->video, []);

        expect($this->video->captions()->count())->toBe(0)
            ->and(Attachment::find($caption->id))->not->toBeNull();
    });
});

describe('rendering', function () {
    beforeEach(function () {
        fakeFfmpeg();
        $this->video = uploadVideo();

        $caption = uploadCaption('en.vtt', "WEBVTT\n");
        AttachmentManager::syncCaptions($this->video, [
            ['caption_id' => $caption->id, 'language' => 'en', 'label' => 'English', 'is_default' => true],
        ]);
    });

    it('renders the video with its size, poster and caption tracks', function () {
        $html = Blade::render('<x-laravel-attachment-library-video :src="$id" class="w-full" />', ['id' => $this->video->id]);

        expect($html)
            ->toContain('width="1920"')
            ->toContain('height="1080"')
            ->toContain('aspect-ratio: 1920 / 1080')
            ->toContain('poster="')
            ->toContain('class="w-full"')
            ->toContain('<source src="' . $this->video->url . '" type="video/mp4">')
            ->toContain('srclang="en"')
            ->toContain('label="English"')
            ->toMatch('/<track[^>]*\sdefault/');
    });

    it('renders nothing for a missing attachment', function () {
        expect(trim(Blade::render('<x-laravel-attachment-library-video :src="999" />')))->toBe('');
    });

    it('exposes video fields in the resource', function () {
        $data = (new AttachmentResource($this->video->load(['poster', 'captions'])))->toArray(new Request());

        expect($data['width'])->toBe(1920)
            ->and($data['height'])->toBe(1080)
            ->and($data['duration'])->toBe(12.5)
            ->and($data['poster']->resource->id)->toBe($this->video->poster_id)
            ->and($data['captions']->all())->toBe([[
                'url' => $this->video->captions->sole()->url,
                'language' => 'en',
                'label' => 'English',
                'is_default' => true,
            ]]);
    });
});

describe('process videos command', function () {
    it('fails when ffmpeg is unavailable', function () {
        fakeMissingFfmpeg();

        $this->artisan('attachment-library:process-videos')->assertFailed();
    });

    it('stores dimensions of existing videos without generating posters', function () {
        fakeMissingFfmpeg();
        $video = uploadVideo();
        fakeFfmpeg();

        $this->artisan('attachment-library:process-videos')->assertSuccessful();

        expect($video->fresh()->width)->toBe(1920)
            ->and($video->fresh()->poster_id)->toBeNull();
    });

    it('generates missing posters when asked', function () {
        fakeMissingFfmpeg();
        $video = uploadVideo();
        fakeFfmpeg();

        $this->artisan('attachment-library:process-videos --posters')->assertSuccessful();

        expect($video->fresh()->poster)->not->toBeNull();
    });
});
