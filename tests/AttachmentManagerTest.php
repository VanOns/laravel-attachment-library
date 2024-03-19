<?php

namespace VanOns\LaravelAttachmentLibrary\Test;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Exceptions\DestinationAlreadyExistsException;
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

        $attachment = self::$attachmentManager->upload($file, null);

        $this->assertEquals(Attachment::find($attachment->id)->get(), self::$attachmentManager->files(null));

        self::$attachmentManager->delete($attachment);

        $this->assertEmpty(self::$attachmentManager->files(null));
        $this->assertEmpty(Attachment::whereDisk(self::$disk)->wherePath(null)->get());
    }

    public function testAssertMoveFile()
    {
        $path = $this->faker->firstName;

        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");
        self::$attachmentManager->createDirectory($path);
        $attachment = self::$attachmentManager->upload($file, null);

        $this->assertNotEmpty(self::$attachmentManager->files(null));
        $this->assertEmpty(self::$attachmentManager->files($path));

        self::$attachmentManager->move($attachment, $path);

        $this->assertEmpty(self::$attachmentManager->files(null));
        $this->assertNotEmpty(self::$attachmentManager->files($path));
    }

    public function testAssertRenameFile()
    {
        $fileNameA = "{$this->faker->firstName}.jpg";
        $fileNameB = "{$this->faker->firstName}.jpg";
        $file = UploadedFile::fake()->image($fileNameA);

        $attachment = self::$attachmentManager->upload($file, null);

        self::assertEquals($attachment->name, $fileNameA);

        self::$attachmentManager->rename($attachment, $fileNameB);

        self::assertEquals($attachment->name, $fileNameB);
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
        $attachment = self::$attachmentManager->upload($file, $directoryNameA);

        $this->assertEquals($attachment->path, $directoryNameA);

        self::$attachmentManager->renameDirectory($directoryNameA, $directoryNameB);

        $attachment->refresh();
        $this->assertEquals($attachment->path, $directoryNameB);
    }

    public function testAssertPreventDuplicateOnUpload()
    {
        self::expectException(DestinationAlreadyExistsException::class);

        $file = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");

        self::$attachmentManager->upload($file, null);
        self::$attachmentManager->upload($file, null);
    }

    public function testAssertPreventDuplicateOnFileRename()
    {
        self::expectException(DestinationAlreadyExistsException::class);

        $fileName = "{$this->faker->firstName}.jpg";
        $fileA = UploadedFile::fake()->image($fileName);
        $fileB = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");

        self::$attachmentManager->upload($fileA, null);
        $attachment = self::$attachmentManager->upload($fileB, null);

        self::$attachmentManager->rename($attachment, $fileName);
    }

    public function testAssertPreventDuplicateOnFileMove()
    {
        self::expectException(DestinationAlreadyExistsException::class);

        $directoryName = $this->faker->firstName;
        self::$attachmentManager->createDirectory($directoryName);

        $fileName = $this->faker->firstName;
        $fileA = UploadedFile::fake()->image($fileName);
        self::$attachmentManager->upload($fileA, $directoryName);

        $fileB = UploadedFile::fake()->image($fileName);
        $attachment = self::$attachmentManager->upload($fileB, null);

        self::$attachmentManager->move($attachment, $directoryName);
    }

    public function testAssertPreventDuplicateOnDirectoryRename()
    {
        self::expectException(DestinationAlreadyExistsException::class);

        $directoryNameA = $this->faker->firstName;
        $directoryNameB = $this->faker->firstName;

        self::$attachmentManager->createDirectory($directoryNameA);
        self::$attachmentManager->createDirectory($directoryNameB);

        self::$attachmentManager->renameDirectory($directoryNameB, $directoryNameA);
    }

    public function testAssertPreventDuplicateOnDirectoryCreate()
    {
        self::expectException(DestinationAlreadyExistsException::class);

        $directoryNameA = $this->faker->firstName;

        self::$attachmentManager->createDirectory($directoryNameA);
        self::$attachmentManager->createDirectory($directoryNameA);
    }

    public function testAssertPreventDuplicateOnMove()
    {
        self::expectException(DestinationAlreadyExistsException::class);

        $fileName = "{$this->faker->firstName}.jpg";
        $fileA = UploadedFile::fake()->image($fileName);
        $fileB = UploadedFile::fake()->image("{$this->faker->firstName}.jpg");

        self::$attachmentManager->upload($fileA, null);
        $attachment = self::$attachmentManager->upload($fileB, null);

        self::$attachmentManager->rename($attachment, $fileName);
    }

    public function testAssertGetUrl()
    {
        $fileName = "{$this->faker->firstName}.jpg";
        $file = UploadedFile::fake()->image($fileName);

        $attachment = self::$attachmentManager->upload($file, null);

        self::assertEquals("/storage/{$fileName}", self::$attachmentManager->getUrl(Attachment::find($attachment->id)));
    }

    public function testAssertDestinationExists()
    {
        $fileName = "{$this->faker->firstName}.jpg";
        $file = UploadedFile::fake()->image($fileName);
        self::$attachmentManager->upload($file, null);

        $directoryName = $this->faker->firstName;
        self::$attachmentManager->createDirectory($directoryName);
        self::$attachmentManager->upload($file, $directoryName);

        self::assertTrue(self::$attachmentManager->destinationExists($fileName));
        self::assertTrue(self::$attachmentManager->destinationExists($directoryName));
        self::assertTrue(self::$attachmentManager->destinationExists("{$directoryName}/{$fileName}"));

        self::assertFalse(self::$attachmentManager->destinationExists('test.jpg'));
        self::assertFalse(self::$attachmentManager->destinationExists('test'));
        self::assertFalse(self::$attachmentManager->destinationExists('test/test.jpg'));
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
        parent::tearDown();

        Storage::fake(self::$disk);
    }
}