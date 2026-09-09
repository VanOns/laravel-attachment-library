<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata\Gd;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

beforeEach(function () {
    Storage::fake('test');
    Config::set('attachment-library.disk', 'test');
});

it('returns false for a text file', function () {
    $file = AttachmentManager::setDisk('test')->upload(
        UploadedFile::fake()->image('test.txt')
    );

    $gd = new Gd();
    expect($gd->getMetadata($file))->toBeFalse();
});

it('returns metadata for a valid image file', function () {
    $file = AttachmentManager::setDisk('test')->upload(
        UploadedFile::fake()->image('test.jpg')
    );

    $gd = new Gd();
    expect($gd->getMetadata($file))->toEqual(
        new FileMetadata('10', '10', bits: 8, channels: 3)
    );
});

it('returns false for a non-existing path', function () {
    $file = Attachment::factory()->make();

    $gd = new Gd();
    expect($gd->getMetadata($file))->toBeFalse();
});

it('caches the resolved metadata', function () {
    $file = AttachmentManager::setDisk('test')->upload(
        UploadedFile::fake()->image('test.jpg')
    );

    $cacheKey = implode('-', ['metadata-adapter', hash('sha256', $file->absolute_path)]);

    $gd = new Gd();

    expect(Cache::get($cacheKey))->toBeEmpty();

    $gd->getMetadata($file);

    expect(Cache::get($cacheKey))->toEqual(
        new FileMetadata('10', '10', bits: 8, channels: 3)
    );
});
