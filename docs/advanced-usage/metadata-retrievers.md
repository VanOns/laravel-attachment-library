# Metadata retrievers

By default, this package provides additional metadata for image files using Gd or Imagick. The Gd adapter is preconfigured, however the Imagick adapter could be dropped in if the configuration on the server allows it.

Change the `metadata_retrievers` content in the `attachment-library.php` configuration file, to make changes to which metadata provider classes are used.

In the code example below, you can see how to implement a new metadata provider.

```php
<?php

namespace App\Adapters\FileMetadata;

use VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata\MetadataAdapter;
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
// config/attachment-library.php
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
