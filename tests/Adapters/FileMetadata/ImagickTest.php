<?php

namespace VanOns\LaravelAttachmentLibrary\Test\Adapters\FileMetadata;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata\Imagick;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;
use VanOns\LaravelAttachmentLibrary\Test\TestCase;

class ImagickTest extends TestCase
{
    use WithFaker;

    public function testAssertTextFile()
    {
        $file = UploadedFile::fake()->create("{$this->faker->firstName}.jpg");
        $imagick = new Imagick();
        $this->assertFalse($imagick->getMetadata($file->path()));
    }

    public function testAssertValidFile()
    {
        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");
        $imagick = new Imagick();
        $this->assertEquals(
            new FileMetadata('10', '10', '96', '96', bits: 8, totalPages: 1),
            $imagick->getMetadata($file->path())
        );
    }

    public function testAssertNonExistingPath()
    {
        $imagick = new Imagick();
        $this->assertFalse($imagick->getMetadata('nonexistentpath :)'));
    }
}
