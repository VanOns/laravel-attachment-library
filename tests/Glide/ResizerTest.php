<?php

namespace VanOns\LaravelAttachmentLibrary\Test\Glide;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Glide\Resizer;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;
use VanOns\LaravelAttachmentLibrary\Test\TestCase;

class ResizerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $attachment;

    public function testAssertCorrectSize()
    {
        $resizer = new Resizer([]);
        $resizer->src($this->attachment)
            ->width(50)
            ->height(150);

        $resizer = $resizer->resize();

        $this->assertSame(50.0, $resizer['width']);
        $this->assertSame(150.0, $resizer['height']);
        $this->assertStringStartsWith('http://localhost/img/test.png', $resizer['url']);
    }

    public function testAssertCorrectWidthByAspectRatio()
    {
        $resizer = new Resizer([]);
        $resizer->src($this->attachment)
            ->height(150)
            ->aspectRatio(.33);

        $resizer = $resizer->resize();

        $this->assertSame(50.0, $resizer['width']);
        $this->assertSame(150.0, $resizer['height']);
        $this->assertStringStartsWith('http://localhost/img/test.png', $resizer['url']);
    }

    public function testAssertCorrectHeightAspectRatio()
    {
        $resizer = new Resizer([]);
        $resizer->src($this->attachment)
            ->width(150)
            ->aspectRatio(.25);

        $resizer = $resizer->resize();

        $this->assertSame(150.0, $resizer['width']);
        $this->assertSame(600.0, $resizer['height']);
        $this->assertStringStartsWith('http://localhost/img/test.png', $resizer['url']);
    }

    public function testAssertWithoutSizing()
    {
        $resizer = new Resizer([]);
        $resizer->src($this->attachment);

        $resizer = $resizer->resize();

        $this->assertNull($resizer['width']);
        $this->assertNull($resizer['height']);
        $this->assertStringStartsWith('http://localhost/img/test.png', $resizer['url']);
    }

    public function testAssertWithoutAspectRatio()
    {
        Storage::fake('test');
        Config::set('attachment-library.disk', 'test');

        $image = UploadedFile::fake()->image('test.png');
        AttachmentManager::setDisk('test')->upload($image, null);

        $resizer = new Resizer([]);
        $resizer->src($this->attachment)->width(250);

        $this->assertEquals(250.0, $resizer->calculateHeight());
    }

    public function testAssertSize()
    {
        $resizer = new Resizer(['full' => 2]);
        $resizer->src($this->attachment)->size('full')->width(250)->height(250);

        $this->assertEquals(500.0, $resizer->calculateWidth());
        $this->assertEquals(500.0, $resizer->calculateHeight());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->be(new User());

        $this->attachment = \Mockery::mock(Attachment::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAttribute')->with('full_path')->andReturn('test.png');
        });
    }

    protected function afterRefreshingDatabase(): void
    {
        $migrations = [
            require (__DIR__.'/../../database/migrations/create_attachments_table.php.stub'),
            require (__DIR__.'/../../database/migrations/create_attachables_table.php.stub'),
        ];

        foreach ($migrations as $migration) {
            $migration->up();
        }
    }
}
