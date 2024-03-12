<?php

namespace VanOns\LaravelAttachmentLibrary\Test;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class AttachmentManagerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected static string $disk = 'test';
    protected static ?AttachmentManager $attachmentManager;

    public function testAssertFilesEmpty()
    {
        $this->assertEmpty(self::$attachmentManager->files(null));
    }

    public function testAssertFilesNotEmpty()
    {
        Attachment::factory()->count(10)->create();

        $this->assertCount(10, self::$attachmentManager->files(null));
    }

    public function testAssertUploadFile()
    {
        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");

        $this->assertEmpty(self::$attachmentManager->files(null));
        $this->assertEmpty(Attachment::whereDisk(self::$disk)->wherePath(null)->get());

        self::$attachmentManager->upload($file, null);

        $this->assertCount(1, self::$attachmentManager->files(null));
        $this->assertCount(1, Attachment::whereDisk(self::$disk)->wherePath(null)->get());
    }

    public function testAssertUploadMultipleFiles()
    {
        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");

        $this->assertEmpty(self::$attachmentManager->files(null));
        $this->assertEmpty(Attachment::whereDisk(self::$disk)->wherePath(null)->get());

        self::$attachmentManager->upload($file, null);

        $this->assertCount(1, self::$attachmentManager->files(null));
        $this->assertCount(1, Attachment::whereDisk(self::$disk)->wherePath(null)->get());

        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");

        self::$attachmentManager->upload($file, null);

        $this->assertCount(2, self::$attachmentManager->files(null));
        $this->assertCount(2, Attachment::whereDisk(self::$disk)->wherePath(null)->get());
    }

    public function testAssertDeleteFile()
    {
        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");

        self::$attachmentManager->upload($file, null);

        $this->assertEquals(Attachment::find(1)->get(), self::$attachmentManager->files(null));

        self::$attachmentManager->delete(Attachment::find(1));

        $this->assertEmpty(self::$attachmentManager->files(null));
        $this->assertEmpty(Attachment::whereDisk(self::$disk)->wherePath(null)->get());
    }

    public function testAssertMoveFile()
    {
        $path = $this->faker->firstName;

        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");
        self::$attachmentManager->createDirectory($path);
        self::$attachmentManager->upload($file, null);

        $this->assertNotEmpty(self::$attachmentManager->files(null));
        $this->assertEmpty(self::$attachmentManager->files($path));

        self::$attachmentManager->move(Attachment::find(1), $path);

        $this->assertEmpty(self::$attachmentManager->files(null));
        $this->assertNotEmpty(self::$attachmentManager->files($path));
    }

    public function testAssertDirectoriesEmpty()
    {
        $this->assertEmpty(self::$attachmentManager->directories());
    }

    public function testAssertDirectoriesNotEmpty()
    {
        $directoryNameA = $this->faker->firstName();
        $directoryNameB = $this->faker->firstName();

        self::$attachmentManager->createDirectory($directoryNameA);

        $this->assertEquals(
            new Collection([$directoryNameA]),
            self::$attachmentManager->directories()
        );

        self::$attachmentManager->createDirectory($directoryNameB);

        $this->assertEqualsCanonicalizing(
            new Collection([$directoryNameA, $directoryNameB]),
            self::$attachmentManager->directories()
        );
    }

    public function testAssertCreateDirectory()
    {
        $directoryName = $this->faker->firstName();

        self::$attachmentManager->createDirectory($directoryName);

        $this->assertEquals(
            new Collection([$directoryName]),
            self::$attachmentManager->directories()
        );
    }

    public function testAssertRemoveDirectory()
    {
        $directoryName = $this->faker->firstName();

        self::$attachmentManager->createDirectory($directoryName);

        $this->assertNotEmpty(self::$attachmentManager->directories());

        self::$attachmentManager->deleteDirectory($directoryName);

        $this->assertEmpty(self::$attachmentManager->directories());
    }

    public function testAssertRemoveDirectoryWithFiles()
    {
        $directoryName = $this->faker->firstName();
        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");

        self::$attachmentManager->createDirectory($directoryName);
        self::$attachmentManager->upload($file, $directoryName);

        $this->assertNotEmpty(self::$attachmentManager->directories());
        $this->assertNotEmpty(self::$attachmentManager->files($directoryName));

        self::$attachmentManager->deleteDirectory($directoryName);

        $this->assertEmpty(self::$attachmentManager->directories());
        $this->assertEmpty(self::$attachmentManager->files($directoryName));
    }

    public function testAssertRenameDirectory()
    {
        $directoryNameA = $this->faker->firstName();
        $directoryNameB = $this->faker->firstName();

        self::$attachmentManager->createDirectory($directoryNameA);

        $this->assertEquals(new Collection([$directoryNameA]), self::$attachmentManager->directories());

        self::$attachmentManager->renameDirectory($directoryNameA, $directoryNameB);

        $this->assertEquals(new Collection([$directoryNameB]), self::$attachmentManager->directories());
    }

    public function testAssertRenameDirectoryWithFiles()
    {
        $directoryNameA = $this->faker->firstName();
        $directoryNameB = $this->faker->firstName();
        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");

        self::$attachmentManager->createDirectory($directoryNameA);
        self::$attachmentManager->upload($file, $directoryNameA);

        $this->assertEquals(Attachment::find(1)->path, $directoryNameA);

        self::$attachmentManager->renameDirectory($directoryNameA, $directoryNameB);

        $this->assertEquals(Attachment::find(1)->path, $directoryNameB);
    }

    public function testAssertPreventDuplicates()
    {
        $directoryNameA = $this->faker->firstName();
        $directoryNameB = $this->faker->firstName();
        $fileNameB = "{$this->faker->firstName}.jpg";

        $fileA = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");
        $fileB = UploadedFile::fake()->image($fileNameB);

        self::$attachmentManager->createDirectory($directoryNameA);
        self::$attachmentManager->createDirectory($directoryNameB);
        self::$attachmentManager->upload($fileA, null);
        self::$attachmentManager->upload($fileB, null);

        self::assertFalse(self::$attachmentManager->upload($fileA, null));
        self::assertFalse(self::$attachmentManager->createDirectory($directoryNameA));
        self::assertFalse(self::$attachmentManager->rename(Attachment::find(1), $fileNameB));
        self::assertFalse(self::$attachmentManager->renameDirectory($directoryNameA, $directoryNameB));

        self::$attachmentManager->move(Attachment::find(1), $directoryNameA);
        self::$attachmentManager->rename(Attachment::find(1), $fileNameB);

        self::assertFalse(self::$attachmentManager->move(Attachment::find(2), $directoryNameA));
    }

    public function testAssertGetUrl()
    {
        $fileName = "{$this->faker->firstName}.jpg";
        $file = UploadedFile::fake()->image($fileName);

        self::$attachmentManager->upload($file, null);

        self::assertEquals("/storage/{$fileName}", self::$attachmentManager->getUrl(Attachment::find(1)));
    }

    public function testAssertSetDisk()
    {
        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");

        self::$attachmentManager->upload($file, null);

        self::assertNotEmpty(self::$attachmentManager->files(null));

        self::$attachmentManager->setDisk($this->faker->firstName);

        self::assertEmpty(self::$attachmentManager->files(null));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(self::$disk);

        Config::set('attachments.disk', self::$disk);
        Config::set('attachments.model', Attachment::class);

        self::$attachmentManager = new AttachmentManager();
    }

    protected function tearDown(): void
    {
        Storage::fake(self::$disk);
    }
}