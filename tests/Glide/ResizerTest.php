<?php

namespace VanOns\LaravelAttachmentLibrary\Test\Glide;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\Enums\Fit;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Glide\Resizer;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;
use VanOns\LaravelAttachmentLibrary\Test\TestCase;

class ResizerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private ?Attachment $defaultAttachment = null;

    private ?Attachment $unresizableAttachment = null;

    public function testAssertCorrectSize()
    {
        $resizer = new Resizer([]);
        $resizer->src($this->defaultAttachment)
            ->fit(Fit::CROP)
            ->width(50)
            ->height(150);

        $resized = $resizer->resize();

        $this->assertSame(50.0, $resized['width']);
        $this->assertSame(150.0, $resized['height']);
        $this->assertStringStartsWith('http://localhost/img/fit=crop,fm=jpg,h=150,w=50/test.png', $resized['url']);
    }

    public function testAssertCorrectWidthByAspectRatio()
    {
        $resizer = new Resizer([]);
        $resizer->src($this->defaultAttachment)
            ->fit(Fit::CROP)
            ->height(150)
            ->aspectRatio(.33);

        $resized = $resizer->resize();

        $this->assertSame(50.0, $resized['width']);
        $this->assertSame(150.0, $resized['height']);
        $this->assertStringStartsWith('http://localhost/img/fit=crop,fm=jpg,h=150,w=50/test.png', $resized['url']);
    }

    public function testAssertCorrectHeightAspectRatio()
    {
        $resizer = new Resizer([]);
        $resizer->src($this->defaultAttachment)
            ->fit(Fit::CROP)
            ->width(150)
            ->aspectRatio(.25);

        $resized = $resizer->resize();

        $this->assertSame(150.0, $resized['width']);
        $this->assertSame(600.0, $resized['height']);
        $this->assertStringStartsWith('http://localhost/img/fit=crop,fm=jpg,h=600,w=150/test.png', $resized['url']);
    }

    public function testAssertWithoutSizing()
    {
        $resizer = new Resizer([]);
        $resizer->src($this->defaultAttachment)
            ->fit(Fit::CROP);

        $resized = $resizer->resize();

        $this->assertNull($resized['width']);
        $this->assertNull($resized['height']);
        $this->assertStringStartsWith('http://localhost/img/fit=crop,fm=jpg,h=,w=/test.png', $resized['url']);
    }

    public function testAssertWithoutAspectRatio()
    {
        $resizer = new Resizer([]);
        $resizer->src($this->defaultAttachment)->width(250);

        $this->assertEquals(250.0, $resizer->calculateHeight());
    }

    public function testAssertSize()
    {
        $resizer = new Resizer(['full' => 2]);
        $resizer->src($this->defaultAttachment)->size('full')->width(250)->height(250);

        $this->assertEquals(500.0, $resizer->calculateWidth());
        $this->assertEquals(500.0, $resizer->calculateHeight());
    }

    public function testAssertUnresizableAttachment()
    {
        $resizer = new Resizer([]);
        $resizer->src($this->unresizableAttachment);

        $resized = $resizer->resize();

        $this->assertEquals([], $resized);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test disk.
        Storage::fake('test');
        Config::set('attachment-library.disk', 'test');
        Config::set('glide.source', Storage::disk('test')->path(''));

        // Set up Glide cache.
        Config::set('glide.cache_disk.root', Storage::disk('test')->path('glide-cache'));
        Storage::disk('test')->makeDirectory('glide-cache');

        $this->be(new User());

        $this->defaultAttachment = $this->defaultAttachment();
        $this->unresizableAttachment = $this->unresizableAttachment();
    }

    protected function afterRefreshingDatabase(): void
    {
        $migrations = [
            require(__DIR__ . '/../../database/migrations/create_attachments_table.php.stub'),
            require(__DIR__ . '/../../database/migrations/create_attachables_table.php.stub'),
        ];

        foreach ($migrations as $migration) {
            $migration->up();
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up Glide cache.
        Storage::disk('test')->deleteDirectory('glide-cache');
    }

    private function defaultAttachment(): Attachment
    {
        return AttachmentManager::setDisk('test')->upload(
            UploadedFile::fake()->image('test.png')
        );
    }

    private function unresizableAttachment(): Attachment
    {
        $svg = <<<'SVG'
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10">
  <rect width="10" height="10" fill="red"/>
</svg>
SVG;

        return AttachmentManager::setDisk('test')->upload(
            UploadedFile::fake()->createWithContent('test.svg', $svg)
                ->mimeType('image/svg+xml')
        );
    }
}
