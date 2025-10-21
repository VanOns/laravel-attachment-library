<p align="center"><img src="art/social-card.png" alt="Social card of Laravel Attachment Library"></p>

# Laravel Attachment Library

[![Latest version on GitHub](https://img.shields.io/github/release/VanOns/laravel-attachment-library.svg?style=flat-square)](https://github.com/VanOns/laravel-attachment-library/releases)
[![Total downloads](https://img.shields.io/packagist/dt/van-ons/laravel-attachment-library.svg?style=flat-square)](https://packagist.org/packages/van-ons/laravel-attachment-library)
[![GitHub issues](https://img.shields.io/github/issues/VanOns/laravel-attachment-library?style=flat-square)](https://github.com/VanOns/laravel-attachment-library/issues)
[![License](https://img.shields.io/github/license/VanOns/laravel-attachment-library?style=flat-square)](https://github.com/VanOns/laravel-attachment-library/blob/main/LICENSE.md)

A Laravel library for attaching files to Eloquent models.

## Quick start

### Installation

The Laravel Attachment Library can be installed using Composer by running the following command:

```bash
$ composer require van-ons/laravel-attachment-library
```

After downloading the dependency, run the following command to install all the migrations and assets:

```bash
$ php artisan attachment-library:install 
```

### Usage

To enable file attachments in your Eloquent models, add the `HasAttachments` trait to your model class:

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

After that you should be able to link attachments to your model as follows:

```php
// Retrieve attachment.
$attachment = \VanOns\LaravelAttachmentLibrary\Models\Attachment::find($attachmentId);

// Retrieve your model.
$myModel = ModelName::find($modelId);

// Link attachment to your model.
$myModel->attachments()->attach($attachment);
```

## Documentation

Please see the [documentation] for detailed information about installation and usage.

## Contributing

Please see [contributing] for more information about how you can contribute.

## Changelog

Please see [changelog] for more information about what has changed recently.

## Upgrading

Please see [upgrading] for more information about how to upgrade.

## Security

Please see [security] for more information about how we deal with security.

## Credits

We would like to thank the following contributors for their contributions to this project:

- [All Contributors][all-contributors]

## License

The scripts and documentation in this project are released under the [MIT License][license].

---

<p align="center"><a href="https://van-ons.nl/" target="_blank"><img src="https://opensource.van-ons.nl/files/cow.png" width="50" alt="Logo of Van Ons"></a></p>

[documentation]: docs/README.md#contents
[contributing]: CONTRIBUTING.md
[changelog]: CHANGELOG.md
[upgrading]: UPGRADING.md
[security]: SECURITY.md
[email]: mailto:opensource@van-ons.nl
[all-contributors]: ../../contributors
[license]: LICENSE.md
