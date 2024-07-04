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
