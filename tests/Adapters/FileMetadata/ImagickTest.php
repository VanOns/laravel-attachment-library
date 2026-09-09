<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata\Imagick;
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

    $imagick = new Imagick();
    expect($imagick->getMetadata($file))->toBeFalse();
});

it('returns metadata for a valid image file', function () {
    $file = AttachmentManager::setDisk('test')->upload(
        UploadedFile::fake()->image('test.jpg')
    );

    $imagick = new Imagick();
    expect($imagick->getMetadata($file))->toEqual(
        new FileMetadata('10', '10', '96', '96', bits: 8, totalPages: 1)
    );
});

it('returns false for a non-existing path', function () {
    $file = Attachment::factory()->make();

    $imagick = new Imagick();
    expect($imagick->getMetadata($file))->toBeFalse();
});
