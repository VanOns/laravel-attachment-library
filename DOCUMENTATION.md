<p align="center"><img src="../art/social-card.png" alt="Social card of Laravel Attachment Library"></p>

# Laravel Attachment Library - Documentation

## Contents
1. [Introduction](#introduction)
2. [Requirements](#requirements)
3. [Installation](#installation)
    1. [Composer](#composer)
    2. [Setup](#setup)
    3. [Configuration](#configuration)
5. [Basic usage](#basic-usage)
   1. [Configuring your model](#configuring-your-model)
   2. [Manage attachments and directories](#manage-attachments-and-directories)
      1. [Attachments](#attachments)
      2. [Directories](#directories)
   3. [Responsive images]()
      1. [Manually resize image]()
12. [Advanced usage]()
    1. [Extending the package]()
    2. [File namers]()
    3. [Metadata retrievers]()

---
## Introduction
The Laravel attachment library is designed to provide an exceptional user experience and an efficient development process for managing files within your Laravel applications.

> [!TIP]
> Take a look at our [Filament Attachment Library](https://github.com/VanOns/filament-attachment-library) which provides a complete integration into the Filament framework.

### Purpose
This package stands out by focusing on the fundamentals, offering a robust foundation while allowing ample room for custom implementations. Whether you need to manage user profile pictures, handle online storage, or integrate file management into any part of your application, this package is built to meet your needs.

### Key Features
- **User-Friendly Interface**: Ensures a smooth, intuitive and fast experience for users.
- **Developer Efficiency**: Simplifies the development process, allowing you to integrate file management quickly and effectively.
- **Customizable**: Provides a solid base with extensive possibilities for custom implementations, enabling you to tailor the package to fit your specific requirements.

### Use Cases
The versatility of this package makes it suitable for a wide range of scenarios, including but not limited to:
- Content management systems
- E-commerce
- Invoice management systems
- Online storage
- Document management
- Educational platforms

By focusing on the essentials and providing flexibility for custom solutions, this package is the ideal choice for developers looking to enhance their applications with efficient and user-friendly file management capabilities.

### Technical details
This package is built on the [Laravel's Flysystem](https://laravel.com/docs/11.x/filesystem) integration.

## Requirements
Before installing and using the Laravel Attachment Library, please ensure your environment meets the requirements below.

### Supported versions
The package has been tested and verified to work with the following versions:
- PHP: 8.2
- Laravel: 11
- Filament: 3.2

### Compatibility
While the package is specifically tested with the versions listed above, it may also work with other versions of PHP, Laravel, and Filament. However, these versions have not been officially tested, and compatibility cannot be guaranteed. If you encounter any issues using different versions, please report them so that we can investigate and potentially extend support.

## Installation
To get started with the Laravel Attachment Library, you will need to follow a few simple steps. This guide will walk you through the process of installing the package and setting it up in your Laravel application.

### Composer
First, you need to add the package to your project using Composer. Open your terminal and run the following command:
```bash
$ composer require van-ons/laravel-attachment-library
```

### Setup
After the package has been added to your project, you need to run the installation command. This command will publish the necessary configuration files and set up the package in your Laravel application.

Run the following artisan command:
```bash
$ php artisan attachment-library:install 
```

### Configuration
By default, this package uses the `public` disk defined in `filesystems.php`. This can be overridden by adding the following to the project's `.env` file:

```env
ATTACHMENTS_DISK=disk_name_here
```

> [!NOTE]
> It is advised to use a disk without any other files. This prevents files from being present without being visible in the file manager.

The `glide.php` and `attachment-library.php` files contain more configuration options.

## Basic usage

### Configuring your model
Accepting attachments for your object requires the use of the trait: `HasAttachments`.

Your model should look like:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use VanOns\LaravelAttachmentLibrary\Concerns\HasAttachments;

class ModelName extends Model
{
    use HasAttachments;
    
    // ...
}
```

#### Attach attachments
You can attach existing attachments to your object in the following way:

```php
// Retrieve attachment.
$attachment = Attachment::find($id);

// Retrieve your model.
$myModel = ModelName::find($id);

// Link attachment to your model.
$myModel->attachments()->attach($attachment);
```

#### Detach attachments
You can detach existing attachments to your object in the following way:

```php
// Retrieve attachment.
$attachment = Attachment::find($id);

// Retrieve your model.
$myModel = ModelName::find($id);

// Detach attachment from your model.
$myModel->attachments()->detach($attachment);
```

### Manage attachments and directories
The AttachmentManager facade is the main part of this package. This class is responsible for uploading, deleting, modifying and moving files while making sure that the database representation stays in sync. 

#### Attachments
The AttachmentManager offers many ways to manage attachments. In this chapter we outline several examples.

Upload a new attachment.
```php
$attachmentModel = AttachmentManager::upload($attachment);
```

Move an attachment to another directory.
```php
$attachmentModel = AttachmentManager::move($attachment, 'new/path');
```

Rename an attachment.
```php
$attachmentModel = AttachmentManager::rename($attachment, 'newName');
```

Delete an attachment.
```php
$attachmentModel = AttachmentManager::delete($attachment);
```

#### Directories
The package also offers ways to manage directories. In this chapter we show some examples.

Create a new directory.
```php
$attachmentModel = AttachmentManager::createDirectory('path/directory-name');
```

Rename an existing directory.
```php
$attachmentModel = AttachmentManager::renameDirectory('path/directory-name', 'new-name');
```

Delete a directory.
> [!WARNING]
> This will also remove any files and subdirectories in the directory. Make sure the directory is empty if you don't want to remove any files.
```php
$attachmentModel = AttachmentManager::deleteDirectory('path/directory-name');
```

### Responsive images
This package includes a Blade component for loading responsive images that scale depending on breakpoints. This ensures optimal performance by delivering appropriately sized images for different screen sizes.

#### Usage
To use the responsive image component, simply include it in your Blade template as follows:

```php
<x-laravel-attachment-library-image :src="$image" />
```

#### Parameters
`src`: The source of the image. This can be a file path in string, an Attachment object, or a numeric ID.

`size`: The size ratio of the image. Available sizes are defined in the `glide.php` config file. 

`aspectRatio`: The aspect ratio of the image.

`class`: Additional CSS-classes.

#### Manually resize image
The Resizer class is responsible for resizing images. It is also possible to call this class directly from other places in the application.

The Resizer class returns an array with width, height and url. The URL should be used as source for the image element. An example code snippet could be:
```php
Resizer::src($image)
    ->height(200)
    ->width(500)
    ->resize();

/**
* [
*   'width' => 500,
*   'height' => 200,
*   'url' => 'http://test.local/img/path-to-image.jpg?w=500&h=200&signature=....'
* ]
*/
```

## Advanced usage

### Extending the package
The package provides the ability to extend several main classes with new or customized functionalities. To extend a class you create a new class that extends the existing class such as:

```php
namespace App\Models;

use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class ExtendedAttachment extends Attachment
{
    // ...
}
```

To ensure that the new object is used throughout the package, the `attachment-library.php` config should be changed to:
```php
<?php

return [
    // ...
    'class_mapping' => [
        'attachment' => \App\Models\ExtendedAttachment::class,
        // ...
    ],
    // ...
];
```

### File namers
It's possible to customize how file names are processed. By default this package offers the `ReplaceControlCharacters` file namer which accepts a search array and replace array. The default configration is as follows:
```php
// config/attachment-library.php
<?php

return [
    // ...
    'file_namers' => [
        \VanOns\LaravelAttachmentLibrary\FileNamers\ReplaceControlCharacters::class => [
            'search' => [
                "/\u{AD}/u",
                "/[\x{00A0}\x{1680}\x{180E}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}\x{FEFF}]/u", // Whitespace characters
                "/\p{C}/u", // To prevent corrupted path exception in WhiteSpaceNormalizer
            ],
            'replace' => [
                '-',
                ' ',
                '',
            ],
        ],
    ],
];
```

The configuration above will replace whitespace characters by regular spaces, soft-hyphens by dashes and strip any action character. The configuration can be tailored to your needs, however the configuration also allows custom file namers. To create a File Namer make sure to extend the `FileNamer` class as follows:
```php
<?php

namespace App\FileNamers;

class ReplaceControlCharacters extends FileNamer
{
    public function execute(string $value): string
    {
        // ...
        return $value;
    }
}
```

The `execute` method will receive the file name without extension as a string and must return a string.

### Metadata retrievers
By default, this package provides additional metadata for image files using Gd or Imagick. The Gd adapter is preconfigured, however the Imagick adapter could be dropped in if the configuration on the server allows it. 

Change the `metadata_retrievers` content in the `attachment-library.php` configuration file, to make changes to which metadata provider classes are used.


In the code example below, you can see how to implement a new metadata provider.
```php
// src/Adapters/ExampleMetadataProvider.php
<?php

namespace App\Adapters\FileMetadata;

use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;

class ExampleMetadataProvider extends MetadataAdapter
{
    protected function retrieve(string $path): FileMetadata|bool
    {
        return new FileMetadata();
    }
}
```

Make sure to change the configuration to activate the metadata provider class.
```php
// config/
<?php

return [
    // ...
    'metadata_retrievers' => [
        // ...
        \App\Adapters\FileMetadata\ExampleMetadataProvider::class => ['image/*'],
    ],
    // ...
];
```

---

<p align="center"><a href="https://van-ons.nl/" target="_blank"><img src="https://opensource.van-ons.nl/files/cow.png" width="50" alt="Logo of Van Ons"></a></p>

[installation]: installation.md
