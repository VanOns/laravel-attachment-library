<?php

namespace VanOns\LaravelAttachmentLibrary\Test;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Enums\DirectoryStrategies;
use VanOns\LaravelAttachmentLibrary\Exceptions\DestinationAlreadyExistsException;
use VanOns\LaravelAttachmentLibrary\Exceptions\DisallowedCharacterException;
use VanOns\LaravelAttachmentLibrary\Exceptions\IncompatibleClassMappingException;
use VanOns\LaravelAttachmentLibrary\Exceptions\NoParentDirectoryException;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class AttachmentManagerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected static string $disk = 'test';

    protected static ?AttachmentManager $attachmentManager;

    public static function fileNameProvider(): array
    {
        return [

            // Valid file names.
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

            // Invalid file names.
            ['t!est.jpg', true],
            ['t/est.jpg', true],
            ['t/est.jpg', true],
            ["te\ts/t.jpg", true],
            ["te\ns/t.jpg", true],
            ['🐄.jpg', true],
            ["'test'.jpg", true],
            ['.jpg', true], // null.

        ];
    }

    public static function pathProvider(): array
    {
        return [

            // Valid paths.
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

            // Invalid paths.
            ['test/t!est', true],
            ['test/t!est/t!est', true],
            ["te\tst/test", true],
            ["te\nst/test", true],
            ["te\ns!t/test", true],
            ['test/🐄', true],
            ["'test'/test", true],
            ['test/', true], // null.

        ];
    }

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

        $directoryA = self::$attachmentManager->createDirectory($directoryNameA);

        $this->assertEquals(
            new Collection([$directoryA]),
            self::$attachmentManager->directories()
        );

        $directoryB = self::$attachmentManager->createDirectory($directoryNameB);

        $this->assertEqualsCanonicalizing(
            new Collection([$directoryA, $directoryB]),
            self::$attachmentManager->directories()
        );
    }

    public function testAssertCreateDirectory()
    {
        $directoryName = $this->faker->firstName();

        $directory = self::$attachmentManager->createDirectory($directoryName);

        $this->assertEquals(
            new Collection([$directory]),
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

        $directoryA = self::$attachmentManager->createDirectory($directoryNameA);

        $this->assertEquals(new Collection([$directoryA]), self::$attachmentManager->directories());

        $directoryB = self::$attachmentManager->renameDirectory($directoryNameA, $directoryNameB);

        $this->assertEquals(new Collection([$directoryB]), self::$attachmentManager->directories());
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

    public function testAssertIncompatibleModel()
    {
        self::expectException(IncompatibleClassMappingException::class);

        $mock = new class extends Model
        {
        };

        Config::set('attachment-library.class_mapping.attachment', $mock::class);

        new AttachmentManager();
    }

    public function testAssertEnsureCompatibleModel()
    {
        self::expectNotToPerformAssertions();

        $mock = new class extends Attachment
        {
        };

        Config::set('attachment-library.class_mapping.attachment', $mock::class);

        new AttachmentManager();
    }

    /**
     * @dataProvider fileNameProvider
     */
    public function testAssertRenameFileWithDisallowedCharacters($name, $expectsException)
    {
        if ($expectsException) {
            self::expectException(DisallowedCharacterException::class);
        }

        $file = UploadedFile::fake()->image($this->faker->firstName);

        $attachment = self::$attachmentManager->upload($file, null);

        self::$attachmentManager->rename($attachment, $name);

        self::assertEquals($attachment->name, $name);
    }

    /**
     * @dataProvider pathProvider
     */
    public function testAssertPathWithDisallowedCharacters($name, $expectsException)
    {
        if ($expectsException) {
            self::expectException(DisallowedCharacterException::class);
        }

        self::$attachmentManager->createDirectory($name, DirectoryStrategies::CREATE_PARENT_DIRECTORIES);
        self::assertTrue(self::$attachmentManager->destinationExists($name));
    }

    public function testAssertNoParentDirectory()
    {
        self::expectException(NoParentDirectoryException::class);

        $path = "{$this->faker->firstName}/{$this->faker->firstName}";
        self::$attachmentManager->createDirectory($path);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(self::$disk);

        Config::set('attachment-library.disk', self::$disk);

        self::$attachmentManager = new AttachmentManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Storage::fake(self::$disk);
    }

    protected function afterRefreshingDatabase(): void
    {
        $migrations = [
            require(__DIR__ . '/../database/migrations/create_attachments_table.php.stub'),
            require(__DIR__ . '/../database/migrations/create_attachables_table.php.stub')
        ];

        foreach($migrations as $migration) {
            $migration->up();
        }
    }
}
