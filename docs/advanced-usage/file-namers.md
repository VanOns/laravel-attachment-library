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
