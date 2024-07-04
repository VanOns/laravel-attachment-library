# Extending the package

The package provides the ability to extend several main classes with new or customized functionalities. To do that, create a new class that extends the existing class::

```php
namespace App\Models;

use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class ExtendedAttachment extends Attachment
{
    // ...
}
```

To ensure that the new object is used throughout the package, the config should be changed to:

```php
// config/attachment-library.php
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
