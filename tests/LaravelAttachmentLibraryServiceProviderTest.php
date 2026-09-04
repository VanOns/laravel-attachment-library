<?php

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;
use VanOns\LaravelAttachmentLibrary\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Exceptions\IncompatibleClassMappingException;
use VanOns\LaravelAttachmentLibrary\LaravelAttachmentLibraryServiceProvider;

it('accepts a compatible attachment manager class map', function () {
    $this->expectNotToPerformAssertions();

    $mock = new class () extends AttachmentManager {
    };

    Config::set('attachment-library.class_mapping.attachment_manager', $mock::class);

    $serviceProvider = new LaravelAttachmentLibraryServiceProvider(Mockery::mock(Application::class));
    $serviceProvider->packageBooted();
});

it('rejects an incompatible attachment manager class map', function () {
    $mock = new class () {
    };

    Config::set('attachment-library.class_mapping.attachment_manager', $mock::class);

    $serviceProvider = new LaravelAttachmentLibraryServiceProvider(Mockery::mock(Application::class));
    $serviceProvider->bootingPackage();
})->throws(IncompatibleClassMappingException::class);
