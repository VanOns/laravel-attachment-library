<?php

namespace VanOns\LaravelAttachmentLibrary\Test;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\LaravelAttachmentLibraryServiceProvider;

class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider
     * @param $app Application
     */
    protected function getPackageProviders($app): array
    {
        return [LaravelAttachmentLibraryServiceProvider::class];
    }

    /**
     * Load package alias
     * @param $app Application
     */
    protected function getPackageAliases($app): array
    {
        return [
            'AttachmentManager' => AttachmentManager::class,
        ];
    }
}