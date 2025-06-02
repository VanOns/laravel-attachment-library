<?php

namespace VanOns\LaravelAttachmentLibrary\Test\Adapters\FileMetadata;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata\Imagick;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;
use VanOns\LaravelAttachmentLibrary\Test\TestCase;

class ImagickTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testAssertTextFile()
    {
        Storage::fake('test');
        Config::set('attachment-library.disk', 'test');

        $file = AttachmentManager::setDisk('test')->upload(
            UploadedFile::fake()->image('test.txt')
        );

        $imagick = new Imagick();
        $this->assertFalse($imagick->getMetadata($file));
    }

    public function testAssertValidFile()
    {
        Storage::fake('test');
        Config::set('attachment-library.disk', 'test');

        $file = AttachmentManager::setDisk('test')->upload(
            UploadedFile::fake()->image('test.jpg')
        );

        $imagick = new Imagick();
        $this->assertEquals(
            new FileMetadata('10', '10', '96', '96', bits: 8, totalPages: 1),
            $imagick->getMetadata($file)
        );
    }

    public function testAssertNonExistingPath()
    {
        Storage::fake('test');
        Config::set('attachment-library.disk', 'test');

        $file = Attachment::factory()->make();

        $imagick = new Imagick();
        $this->assertFalse($imagick->getMetadata($file));
    }

    protected function afterRefreshingDatabase(): void
    {
        $migrations = [
            require (__DIR__.'/../../../database/migrations/create_attachments_table.php.stub'),
            require (__DIR__.'/../../../database/migrations/create_attachables_table.php.stub'),
        ];

        foreach ($migrations as $migration) {
            $migration->up();
        }
    }
}
