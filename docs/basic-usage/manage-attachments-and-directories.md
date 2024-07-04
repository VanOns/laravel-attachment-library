# Manage attachments and directories

The AttachmentManager facade is the main part of this package. This class is responsible for uploading, deleting, modifying and moving files while making sure that the database representation stays in sync.

#### Attachments

The AttachmentManager offers many ways to manage attachments.

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

The package also offers ways to manage directories.

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
