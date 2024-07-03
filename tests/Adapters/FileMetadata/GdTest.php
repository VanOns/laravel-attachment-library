<?php

namespace VanOns\LaravelAttachmentLibrary\Test\Adapters\FileMetadata;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata\Gd;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;
use VanOns\LaravelAttachmentLibrary\Test\TestCase;

class GdTest extends TestCase
{
    use WithFaker;

    public function testAssertTextFile()
    {
        $file = UploadedFile::fake()->create("{$this->faker->firstName}.jpg");
        $gd = new Gd();
        $this->assertFalse($gd->getMetadata($file->path()));
    }

    public function testAssertValidFile()
    {
        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");
        $gd = new Gd();
        $this->assertEquals(
            new FileMetadata('10', '10', bits: 8, channels: 3),
            $gd->getMetadata($file->path())
        );
    }

    public function testAssertNonExistingPath()
    {
        $gd = new Gd();
        $this->assertFalse($gd->getMetadata('nonexistentpath :)'));
    }

    public function testAssertCache()
    {
        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");
        $cacheKey = implode('-', ['image-adapter', hash('sha256', $file->path())]);

        $gd = new Gd();

        $this->assertEmpty(Cache::get($cacheKey));

        $gd->getMetadata($file->path());

        $this->assertEquals(
            new FileMetadata('10', '10', bits: 8, channels: 3),
            Cache::get($cacheKey)
        );
    }
}
