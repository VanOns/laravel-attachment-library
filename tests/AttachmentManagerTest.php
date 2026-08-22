<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Enums\AttachmentType;
use VanOns\LaravelAttachmentLibrary\Enums\DirectoryStrategies;
use VanOns\LaravelAttachmentLibrary\Exceptions\DestinationAlreadyExistsException;
use VanOns\LaravelAttachmentLibrary\Exceptions\DisallowedCharacterException;
use VanOns\LaravelAttachmentLibrary\Exceptions\IncompatibleClassMappingException;
use VanOns\LaravelAttachmentLibrary\Exceptions\NoParentDirectoryException;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

beforeEach(function () {
    Storage::fake('test');

    Config::set('attachment-library.disk', 'test');
    Config::set('attachment-library.attachment_mime_type_mapping', [
        AttachmentType::PREVIEWABLE_IMAGE => ['image/jpeg'],
    ]);

    $this->be(new User());
});

it('has no files when empty', function () {
    expect((new AttachmentManager())->files(null))->toBeEmpty();
});

it('has files after creating them', function () {
    Attachment::factory()->count(10)->create();

    expect((new AttachmentManager())->files(null))->toHaveCount(10);
});

it('uploads a file', function () {
    $attachmentManager = new AttachmentManager();
    $file = UploadedFile::fake()->image(fake()->word() . '.jpg');

    expect($attachmentManager->files(null))->toBeEmpty();
    expect(Attachment::whereDisk('test')->wherePath(null)->get())->toBeEmpty();

    $attachmentManager->upload($file, null);

    expect($attachmentManager->files(null))->toHaveCount(1);
    expect(Attachment::whereDisk('test')->wherePath(null)->get())->toHaveCount(1);
});

it('uploads multiple files', function () {
    $attachmentManager = new AttachmentManager();
    $file = UploadedFile::fake()->image(fake()->word() . '.jpg');

    expect($attachmentManager->files(null))->toBeEmpty();
    expect(Attachment::whereDisk('test')->wherePath(null)->get())->toBeEmpty();

    $attachmentManager->upload($file, null);

    expect($attachmentManager->files(null))->toHaveCount(1);
    expect(Attachment::whereDisk('test')->wherePath(null)->get())->toHaveCount(1);

    $file = UploadedFile::fake()->image(fake()->word() . '.jpg');

    $attachmentManager->upload($file, null);

    expect($attachmentManager->files(null))->toHaveCount(2);
    expect(Attachment::whereDisk('test')->wherePath(null)->get())->toHaveCount(2);
});

it('deletes a file', function () {
    $attachmentManager = new AttachmentManager();
    $file = UploadedFile::fake()->image(fake()->word() . '.jpg');

    $attachment = $attachmentManager->upload($file, null);

    expect(Attachment::find($attachment->id)->get())->toEqual($attachmentManager->files(null));

    $attachmentManager->delete($attachment);

    expect($attachmentManager->files(null))->toBeEmpty();
    expect(Attachment::whereDisk('test')->wherePath(null)->get())->toBeEmpty();
});

it('moves a file', function () {
    $attachmentManager = new AttachmentManager();
    $path = fake()->word();

    $file = UploadedFile::fake()->image(fake()->word() . '.jpg');
    $attachmentManager->createDirectory($path);
    $attachment = $attachmentManager->upload($file, null);

    expect($attachmentManager->files(null))->not->toBeEmpty();
    expect($attachmentManager->files($path))->toBeEmpty();

    $attachmentManager->move($attachment, $path);

    expect($attachmentManager->files(null))->toBeEmpty();
    expect($attachmentManager->files($path))->not->toBeEmpty();
});

it('renames a file', function () {
    $attachmentManager = new AttachmentManager();
    $fileNameA = fake()->word() . '.jpg';
    $fileNameB = fake()->word();
    $file = UploadedFile::fake()->image($fileNameA);

    $attachment = $attachmentManager->upload($file, null);

    expect($attachment->filename)->toBe($fileNameA);

    $attachmentManager->rename($attachment, $fileNameB);

    expect($attachment->filename)->toBe("{$fileNameB}.jpg");
});

it('has no directories when empty', function () {
    expect((new AttachmentManager())->directories())->toBeEmpty();
});

it('has directories after creating them', function () {
    $attachmentManager = new AttachmentManager();
    $directoryNameA = fake()->word();
    $directoryNameB = fake()->word();

    $directoryA = $attachmentManager->createDirectory($directoryNameA);

    expect($attachmentManager->directories())->toEqual(new Collection([$directoryA]));

    $directoryB = $attachmentManager->createDirectory($directoryNameB);

    expect($attachmentManager->directories()->all())->toEqualCanonicalizing(
        (new Collection([$directoryA, $directoryB]))->all()
    );
});

it('creates a directory', function () {
    $attachmentManager = new AttachmentManager();
    $directoryName = fake()->word();

    $directory = $attachmentManager->createDirectory($directoryName);

    expect($attachmentManager->directories())->toEqual(new Collection([$directory]));
});

it('removes a directory', function () {
    $attachmentManager = new AttachmentManager();
    $directoryName = fake()->word();

    $attachmentManager->createDirectory($directoryName);

    expect($attachmentManager->directories())->not->toBeEmpty();

    $attachmentManager->deleteDirectory($directoryName);

    expect($attachmentManager->directories())->toBeEmpty();
});

it('removes a directory with files', function () {
    $attachmentManager = new AttachmentManager();
    $directoryName = fake()->word();
    $file = UploadedFile::fake()->image(fake()->word() . '.jpg');

    $attachmentManager->createDirectory($directoryName);
    $attachmentManager->upload($file, $directoryName);

    expect($attachmentManager->directories())->not->toBeEmpty();
    expect($attachmentManager->files($directoryName))->not->toBeEmpty();

    $attachmentManager->deleteDirectory($directoryName);

    expect($attachmentManager->directories())->toBeEmpty();
    expect($attachmentManager->files($directoryName))->toBeEmpty();
});

it('renames a directory', function () {
    $attachmentManager = new AttachmentManager();
    $directoryNameA = fake()->word();
    $directoryNameB = fake()->word();

    $directoryA = $attachmentManager->createDirectory($directoryNameA);

    expect($attachmentManager->directories())->toEqual(new Collection([$directoryA]));

    $directoryB = $attachmentManager->renameDirectory($directoryNameA, $directoryNameB);

    expect($attachmentManager->directories())->toEqual(new Collection([$directoryB]));
});

it('renames a directory with files', function () {
    $attachmentManager = new AttachmentManager();
    $directoryNameA = fake()->word();
    $directoryNameB = fake()->word();
    $file = UploadedFile::fake()->image(fake()->word() . '.jpg');

    $attachmentManager->createDirectory($directoryNameA);
    $attachment = $attachmentManager->upload($file, $directoryNameA);

    expect($attachment->path)->toBe($directoryNameA);

    $attachmentManager->renameDirectory($directoryNameA, $directoryNameB);

    $attachment->refresh();
    expect($attachment->path)->toBe($directoryNameB);
});

it('prevents a duplicate on upload', function () {
    $attachmentManager = new AttachmentManager();
    $file = UploadedFile::fake()->image(fake()->word() . '.jpg');

    $attachmentManager->upload($file, null);
    $attachmentManager->upload($file, null);
})->throws(DestinationAlreadyExistsException::class);

it('prevents a duplicate on file rename', function () {
    $attachmentManager = new AttachmentManager();
    $fileName = fake()->word();
    $fileA = UploadedFile::fake()->image("{$fileName}.jpg");
    $fileB = UploadedFile::fake()->image(fake()->word() . '.jpg');

    $attachmentManager->upload($fileA, null);
    $attachment = $attachmentManager->upload($fileB, null);

    $attachmentManager->rename($attachment, $fileName);
})->throws(DestinationAlreadyExistsException::class);

it('prevents a duplicate on file move', function () {
    $attachmentManager = new AttachmentManager();
    $directoryName = fake()->word();
    $attachmentManager->createDirectory($directoryName);

    $fileName = fake()->word() . '.jpg';
    $fileA = UploadedFile::fake()->image($fileName);
    $attachmentManager->upload($fileA, $directoryName);

    $fileB = UploadedFile::fake()->image($fileName);
    $attachment = $attachmentManager->upload($fileB, null);

    $attachmentManager->move($attachment, $directoryName);
})->throws(DestinationAlreadyExistsException::class);

it('prevents a duplicate on directory rename', function () {
    $attachmentManager = new AttachmentManager();
    $directoryNameA = fake()->word();
    $directoryNameB = fake()->word();

    $attachmentManager->createDirectory($directoryNameA);
    $attachmentManager->createDirectory($directoryNameB);

    $attachmentManager->renameDirectory($directoryNameB, $directoryNameA);
})->throws(DestinationAlreadyExistsException::class);

it('prevents a duplicate on directory create', function () {
    $attachmentManager = new AttachmentManager();
    $directoryNameA = fake()->word();

    $attachmentManager->createDirectory($directoryNameA);
    $attachmentManager->createDirectory($directoryNameA);
})->throws(DestinationAlreadyExistsException::class);

it('resolves the url', function () {
    $attachmentManager = new AttachmentManager();
    $fileName = fake()->word() . '.jpg';
    $file = UploadedFile::fake()->image($fileName);

    $attachment = $attachmentManager->upload($file, null);

    expect($attachmentManager->getUrl(Attachment::find($attachment->id)))->toBe(url("/files/{$fileName}"));
});

it('resolves the absolute path', function () {
    $attachmentManager = new AttachmentManager();
    $fileName = fake()->word() . '.jpg';
    $file = UploadedFile::fake()->image($fileName);

    $attachment = $attachmentManager->upload($file, null);

    expect($attachment->absolute_path)->toBe(Storage::disk('test')->path($fileName));
});

it('determines the attachment type', function () {
    $attachmentManager = new AttachmentManager();
    $fileNameA = fake()->word() . '.jpg';
    $fileA = UploadedFile::fake()->image($fileNameA);

    $attachmentA = $attachmentManager->upload($fileA, null);

    $fileNameB = fake()->word() . '.txt';
    $fileB = UploadedFile::fake()->create($fileNameB);

    $attachmentB = $attachmentManager->upload($fileB, null);

    expect($attachmentManager->isType($attachmentA, AttachmentType::PREVIEWABLE_IMAGE))->toBeTrue();
    expect($attachmentManager->isType($attachmentB, AttachmentType::PREVIEWABLE_IMAGE))->toBeFalse();
});

it('checks whether a destination exists', function () {
    $attachmentManager = new AttachmentManager();
    $fileName = fake()->word() . '.jpg';
    $file = UploadedFile::fake()->image($fileName);
    $attachmentManager->upload($file, null);

    $directoryName = fake()->word();
    $attachmentManager->createDirectory($directoryName);
    $attachmentManager->upload($file, $directoryName);

    expect($attachmentManager->destinationExists($fileName))->toBeTrue();
    expect($attachmentManager->destinationExists($directoryName))->toBeTrue();
    expect($attachmentManager->destinationExists("{$directoryName}/{$fileName}"))->toBeTrue();

    expect($attachmentManager->destinationExists('test.jpg'))->toBeFalse();
    expect($attachmentManager->destinationExists('test'))->toBeFalse();
    expect($attachmentManager->destinationExists('test/test.jpg'))->toBeFalse();
});

it('resets the file list when switching disks', function () {
    $attachmentManager = new AttachmentManager();
    $file = UploadedFile::fake()->image(fake()->word() . '.jpg');

    $attachmentManager->upload($file, null);

    expect($attachmentManager->files(null))->not->toBeEmpty();

    $attachmentManager->setDisk(fake()->word());

    expect($attachmentManager->files(null))->toBeEmpty();
});

it('rejects an incompatible model class mapping', function (string $config) {
    $mock = new class () {
    };

    Config::set($config, $mock::class);

    new AttachmentManager();
})->with([
    ['attachment-library.class_mapping.attachment'],
    ['attachment-library.class_mapping.directory'],
])->throws(IncompatibleClassMappingException::class);

it('accepts a compatible model class mapping', function (string $config, object $mock) {
    $this->expectNotToPerformAssertions();

    Config::set($config, $mock::class);

    new AttachmentManager();
})->with([
    ['attachment-library.class_mapping.attachment', new class () extends Attachment {
    }],
]);

it('rejects a disallowed character on file rename', function (string $name, bool $expectsException) {
    $attachmentManager = new AttachmentManager();

    if ($expectsException) {
        $this->expectException(DisallowedCharacterException::class);
    }

    $file = UploadedFile::fake()->image(fake()->word() . '.jpg');

    $attachment = $attachmentManager->upload($file, null);

    $attachmentManager->rename($attachment, $name);

    expect($attachment->name)->toBe($name);
})->with([
    ['test.jpg', false],
    ['t-est.jpg', false],
    ['t_est.jpg', false],
    ['tést.jpg', false],
    ['.env', false],
    ['.jpg', false],
    ['test', false],
    ['诶.jpg', false],
    ['t est.jpg', false],
    ['t est.jpg', false], // Non-breaking space.
    ['t!est.jpg', true],
    ['t/est.jpg', true],
    ['t/est.jpg', true],
    ["te\ts/t.jpg", true],
    ["te\ns/t.jpg", true],
    ['🐄.jpg', true],
    ["'test'.jpg", true],
    ["\u{E000}.jpg", true], // Private-use-area character renders as invisible.
]);

it('rejects a disallowed character on directory path', function (string $name, bool $expectsException) {
    $attachmentManager = new AttachmentManager();

    if ($expectsException) {
        $this->expectException(DisallowedCharacterException::class);
    }

    $attachmentManager->createDirectory($name, DirectoryStrategies::CREATE_PARENT_DIRECTORIES);
    expect($attachmentManager->destinationExists($name))->toBeTrue();
})->with([
    ['test/test', false],
    ['t-est', false],
    ['t_est', false],
    ['tést', false],
    ['诶', false],
    ['test/.test', false],
    ['.test/test', false],
    ['test', false],
    ['test/t est', false],
    ['test/t est/test', false], // Non-breaking space.
    ['test/t!est', true],
    ['test/t!est/t!est', true],
    ["te\tst/test", true],
    ["te\nst/test", true],
    ["te\ns!t/test", true],
    ['test/🐄', true],
    ["'test'/test", true],
    ["test/\u{E000}", true], // Private-use-area character renders as invisible.
]);

it('requires a parent directory', function () {
    $attachmentManager = new AttachmentManager();
    $path = fake()->word() . '/' . fake()->word();

    $attachmentManager->createDirectory($path);
})->throws(NoParentDirectoryException::class);
