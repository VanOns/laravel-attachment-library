# File namers

It's possible to customize how filenames are processed. By default, this package offers the `ReplaceControlCharacters`
file namer which accepts a search array and replace array. The default configuration is as follows:

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

The configuration above will replace whitespace characters with regular spaces, soft-hyphens with dashes, and strip any
action character. The configuration can be tailored to your needs, but the configuration also allows custom file namers.
To create a file namer, make sure to extend the `FileNamer` class as follows:

```php
<?php

namespace App\FileNamers;

use VanOns\LaravelAttachmentLibrary\FileNamers\FileNamer;

class YourFileNamer extends FileNamer
{
    public function execute(string $value): string
    {
        // ...
        return $value;
    }
}
```

The `execute` method will receive the filename without extension as a string and must return a string.
