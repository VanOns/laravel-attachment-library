<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\Enums\Fit;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Glide\Resizer;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

function createResizerTestAttachment(): Attachment
{
    return AttachmentManager::setDisk('test')->upload(
        UploadedFile::fake()->image('test.png')
    );
}

function createUnresizableResizerTestAttachment(): Attachment
{
    $svg = <<<'SVG'
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10">
  <rect width="10" height="10" fill="red"/>
</svg>
SVG;

    return AttachmentManager::setDisk('test')->upload(
        UploadedFile::fake()->createWithContent('test.svg', $svg)
            ->mimeType('image/svg+xml')
    );
}

beforeEach(function () {
    Storage::fake('test');
    Config::set('attachment-library.disk', 'test');
    Config::set('glide.source', Storage::disk('test')->path(''));

    Config::set('glide.cache_disk.root', Storage::disk('test')->path('glide-cache'));
    Storage::disk('test')->makeDirectory('glide-cache');

    $this->be(new User());
});

afterEach(function () {
    Storage::disk('test')->deleteDirectory('glide-cache');
});

it('calculates the correct size', function () {
    $resizer = new Resizer([]);
    $resizer->src(createResizerTestAttachment())
        ->fit(Fit::CROP)
        ->width(50)
        ->height(150);

    $resized = $resizer->resize();

    expect($resized['width'])->toBe(50.0);
    expect($resized['height'])->toBe(150.0);
    expect($resized['url'])->toStartWith('http://localhost/img/fit=crop,fm=jpg,h=150,w=50/test.png');
});

it('calculates the correct width by aspect ratio', function () {
    $resizer = new Resizer([]);
    $resizer->src(createResizerTestAttachment())
        ->fit(Fit::CROP)
        ->height(150)
        ->aspectRatio(.33);

    $resized = $resizer->resize();

    expect($resized['width'])->toBe(50.0);
    expect($resized['height'])->toBe(150.0);
    expect($resized['url'])->toStartWith('http://localhost/img/fit=crop,fm=jpg,h=150,w=50/test.png');
});

it('calculates the correct height by aspect ratio', function () {
    $resizer = new Resizer([]);
    $resizer->src(createResizerTestAttachment())
        ->fit(Fit::CROP)
        ->width(150)
        ->aspectRatio(.25);

    $resized = $resizer->resize();

    expect($resized['width'])->toBe(150.0);
    expect($resized['height'])->toBe(600.0);
    expect($resized['url'])->toStartWith('http://localhost/img/fit=crop,fm=jpg,h=600,w=150/test.png');
});

it('resizes without explicit sizing', function () {
    $resizer = new Resizer([]);
    $resizer->src(createResizerTestAttachment())
        ->fit(Fit::CROP);

    $resized = $resizer->resize();

    expect($resized['width'])->toBeNull();
    expect($resized['height'])->toBeNull();
    expect($resized['url'])->toStartWith('http://localhost/img/fit=crop,fm=jpg,h=,w=/test.png');
});

it('calculates height without an aspect ratio', function () {
    $resizer = new Resizer([]);
    $resizer->src(createResizerTestAttachment())->width(250);

    expect($resizer->calculateHeight())->toBe(250.0);
});

it('applies the size multiplier', function () {
    $resizer = new Resizer(['full' => 2]);
    $resizer->src(createResizerTestAttachment())->size('full')->width(250)->height(250);

    expect($resizer->calculateWidth())->toBe(500.0);
    expect($resizer->calculateHeight())->toBe(500.0);
});

it('returns an empty result for an unresizable attachment', function () {
    $resizer = new Resizer([]);
    $resizer->src(createUnresizableResizerTestAttachment());

    $resized = $resizer->resize();

    expect($resized)->toBe([]);
});
