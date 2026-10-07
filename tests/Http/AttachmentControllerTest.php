<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\Enums\AttachmentType;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;

beforeEach(function () {
    Storage::fake('test');
    Config::set('attachment-library.disk', 'test');
    Config::set('attachment-library.attachment_mime_type_mapping', [
        AttachmentType::PREVIEWABLE_VIDEO => ['video/mp4'],
    ]);
    fakeMissingFfmpeg();
});

it('serves part of a local file for a range request', function () {
    AttachmentManager::setDisk('test')->upload(UploadedFile::fake()->createWithContent('clip.mp4', str_repeat('a', 4096))->mimeType('video/mp4'));

    $response = $this->get('/files/clip.mp4', ['Range' => 'bytes=0-1023']);

    $response->assertStatus(206)
        ->assertHeader('Content-Range', 'bytes 0-1023/4096')
        ->assertHeader('Content-Length', '1024')
        ->assertHeader('Content-Type', 'video/mp4');
});

it('serves renderable files inline and others as a download', function () {
    AttachmentManager::setDisk('test')->upload(UploadedFile::fake()->createWithContent('clip.mp4', 'video')->mimeType('video/mp4'));
    AttachmentManager::setDisk('test')->upload(UploadedFile::fake()->createWithContent('data.csv', 'a,b'));

    expect($this->get('/files/clip.mp4')->headers->get('Content-Disposition'))->toStartWith('inline')
        ->and($this->get('/files/data.csv')->headers->get('Content-Disposition'))->toStartWith('attachment');
});

it('streams files from a remote disk', function () {
    Storage::fake('remote');
    Config::set('filesystems.disks.remote.driver', 's3');
    AttachmentManager::setDisk('remote')->upload(UploadedFile::fake()->createWithContent('clip.mp4', 'remote video')->mimeType('video/mp4'));

    $response = $this->get('/files/clip.mp4');

    $response->assertOk()->assertHeader('Content-Type', 'video/mp4');
    expect($response->streamedContent())->toBe('remote video');
});
