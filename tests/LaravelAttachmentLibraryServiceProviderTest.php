<?php

namespace VanOns\LaravelAttachmentLibrary\Test;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Mockery;
use VanOns\LaravelAttachmentLibrary\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Exceptions\IncompatibleClassMappingException;
use VanOns\LaravelAttachmentLibrary\LaravelAttachmentLibraryServiceProvider;

class LaravelAttachmentLibraryServiceProviderTest extends TestCase
{
    use WithFaker;

    protected static ?Application $applicationMock;

    public function testAssertCompatibleAttachmentManagerClassMap()
    {
        self::expectNotToPerformAssertions();

        $mock = new class () extends AttachmentManager {
        };

        Config::set('attachment-library.class_mapping.attachment_manager', $mock::class);

        $serviceProvider = new LaravelAttachmentLibraryServiceProvider(self::$applicationMock);
        $serviceProvider->packageBooted();
    }

    public function testAssertIncompatibleAttachmentManagerClassMap()
    {
        self::expectException(IncompatibleClassMappingException::class);

        $mock = new class () {
        };

        Config::set('attachment-library.class_mapping.attachment_manager', $mock::class);

        $serviceProvider = new LaravelAttachmentLibraryServiceProvider(self::$applicationMock);
        $serviceProvider->bootingPackage();
    }

    protected function setUp(): void
    {
        parent::setUp();

        self::$applicationMock = Mockery::mock(Application::class);
    }
}
