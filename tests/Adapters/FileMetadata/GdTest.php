<?php

namespace VanOns\LaravelAttachmentLibrary\Test\Adapters\FileMetadata;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata\Gd;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;
use VanOns\LaravelAttachmentLibrary\Test\TestCase;

class GdTest extends TestCase
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

        $gd = new Gd();
        $this->assertFalse($gd->getMetadata($file));
    }

    public function testAssertValidFile()
    {
        Storage::fake('test');
        Config::set('attachment-library.disk', 'test');

        $file = AttachmentManager::setDisk('test')->upload(
            UploadedFile::fake()->image('test.jpg')
        );

        $gd = new Gd();
        $this->assertEquals(
            new FileMetadata('10', '10', bits: 8, channels: 3),
            $gd->getMetadata($file)
        );
    }

    public function testAssertNonExistingPath()
    {
        Storage::fake('test');
        Config::set('attachment-library.disk', 'test');

        $file = Attachment::factory()->make();

        $gd = new Gd();
        $this->assertFalse($gd->getMetadata($file));
    }

    public function testAssertCache()
    {
        Storage::fake('test');
        Config::set('attachment-library.disk', 'test');

        $file = AttachmentManager::setDisk('test')->upload(
            UploadedFile::fake()->image('test.jpg')
        );

        $cacheKey = implode('-', ['metadata-adapter', hash('sha256', $file->absolute_path)]);

        $gd = new Gd();

        $this->assertEmpty(Cache::get($cacheKey));

        $gd->getMetadata($file);

        $this->assertEquals(
            new FileMetadata('10', '10', bits: 8, channels: 3),
            Cache::get($cacheKey)
        );
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
