<?php

namespace VanOns\LaravelAttachmentLibrary\Test\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\Enums\Fit;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;
use VanOns\LaravelAttachmentLibrary\Test\TestCase;

class GlidePresetControllerTest extends TestCase
{
    use RefreshDatabase;

    private ?Attachment $attachment = null;

    public function testRejectsUnconfiguredFitPreset()
    {
        $this->get('/img/square/1024/webp/contain/test.png')->assertForbidden();
    }

    public function testRejectsMalformedFit()
    {
        $this->get('/img/square/1024/webp/crop-1-2-3/test.png')->assertForbidden();
    }

    public function testGeneratesImageForConfiguredFitPreset()
    {
        Config::set('glide.fit_presets', ['contain' => Fit::CONTAIN->value]);

        $this->get('/img/square/1024/webp/contain/test.png')->assertOk();

        $this->assertTrue(
            Storage::disk('test')->exists('glide-cache/square/1024/webp/contain/test.png')
        );
    }

    public function testCachesUnderRequestedFitInsteadOfResolvedFit()
    {
        $this->attachment->update(['focal_point' => ['x' => 25, 'y' => 75]]);

        $this->get('/img/square/1024/webp/crop/test.png')->assertOk();

        // The focal point determines the rendered image, but not the path it is cached under.
        $this->assertTrue(
            Storage::disk('test')->exists('glide-cache/square/1024/webp/crop/test.png')
        );
    }

    public function testAcceptsLegacyFocalPointFit()
    {
        $this->get('/img/square/1024/webp/crop-25-75/test.png')->assertOk();

        $this->assertTrue(
            Storage::disk('test')->exists('glide-cache/square/1024/webp/crop-25-75/test.png')
        );
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

        $this->attachment = AttachmentManager::setDisk('test')->upload(
            UploadedFile::fake()->image('test.png')
        );
    }

    protected function afterRefreshingDatabase(): void
    {
        $migrations = [
            require(__DIR__ . '/../../database/migrations/create_attachments_table.php.stub'),
            require(__DIR__ . '/../../database/migrations/create_attachables_table.php.stub'),
            require(__DIR__ . '/../../database/migrations/add_focal_point_to_attachments_table.php.stub'),
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

    /**
     * The routes use the `web` middleware, which requires an encryption key.
     *
     * @param  $app  Application
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
    }
}
