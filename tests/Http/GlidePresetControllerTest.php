<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\Enums\Fit;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;

beforeEach(function () {
    Storage::fake('test');
    Config::set('attachment-library.disk', 'test');
    Config::set('glide.source', Storage::disk('test')->path(''));

    Config::set('glide.cache_disk.root', Storage::disk('test')->path('glide-cache'));
    Storage::disk('test')->makeDirectory('glide-cache');

    $this->attachment = AttachmentManager::setDisk('test')->upload(
        UploadedFile::fake()->image('test.png')
    );
});

afterEach(function () {
    Storage::disk('test')->deleteDirectory('glide-cache');
});

it('rejects an unconfigured fit preset', function () {
    $this->get('/img/square/1024/webp/contain/test.png')->assertForbidden();
});

it('rejects a malformed fit', function () {
    $this->get('/img/square/1024/webp/crop-1-2-3/test.png')->assertForbidden();
});

it('generates an image for a configured fit preset', function () {
    Config::set('glide.fit_presets', ['contain' => Fit::CONTAIN->value]);

    $this->get('/img/square/1024/webp/contain/test.png')->assertOk();

    expect(Storage::disk('test')->exists('glide-cache/square/1024/webp/contain/test.png'))->toBeTrue();
});

it('caches under the requested fit instead of the resolved fit', function () {
    $this->attachment->update(['focal_point' => ['x' => 25, 'y' => 75]]);

    $this->get('/img/square/1024/webp/crop/test.png')->assertOk();

    expect(Storage::disk('test')->exists('glide-cache/square/1024/webp/crop/test.png'))->toBeTrue();
});

it('accepts a legacy focal point fit', function () {
    $this->get('/img/square/1024/webp/crop-25-75/test.png')->assertOk();

    expect(Storage::disk('test')->exists('glide-cache/square/1024/webp/crop-25-75/test.png'))->toBeTrue();
});
