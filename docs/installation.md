# Installation

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
