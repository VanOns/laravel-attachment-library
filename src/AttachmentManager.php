<?php

namespace VanOns\LaravelAttachmentLibrary;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\Directory;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\Filename;
use VanOns\LaravelAttachmentLibrary\Enums\DirectoryStrategies;
use VanOns\LaravelAttachmentLibrary\Exceptions\DestinationAlreadyExistsException;
use VanOns\LaravelAttachmentLibrary\Exceptions\DisallowedCharacterException;
use VanOns\LaravelAttachmentLibrary\Exceptions\IncompatibleClassMappingException;
use VanOns\LaravelAttachmentLibrary\Exceptions\NoParentDirectoryException;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

/**
 * Performs attachment related actions on database and filesystem.
 */
class AttachmentManager
{
    protected string $disk;

    protected string $attachmentClass;

    protected string $directoryClass;

    protected string $allowedCharacters;

    protected array $attachmentTypeMapping;

    /**
     * @throws IncompatibleClassMappingException
     */
    public function __construct()
    {
        $this->disk = Config::get('attachment-library.disk', 'public');
        $this->attachmentClass = Config::get('attachment-library.class_mapping.attachment', Attachment::class);
        $this->directoryClass = Config::get('attachment-library.class_mapping.directory', Directory::class);
        $this->attachmentTypeMapping = Config::get('attachment-library.attachment_mime_type_mapping', []);
        $this->allowedCharacters = Config::get('attachment-library.allowed_characters', '/[^\\pL\\pN_\.\- ]+/u');

        $this->ensureCompatibleClasses();
    }

    /**
     * Throw exception if configured model is not an instance of the Attachment model.
     *
     * @throws IncompatibleClassMappingException
     */
    protected function ensureCompatibleClasses(): void
    {
        if (! is_a($this->attachmentClass, Attachment::class, true)) {
            throw new IncompatibleClassMappingException($this->attachmentClass, Attachment::class);
        }
        if (! is_a($this->directoryClass, Directory::class, true)) {
            throw new IncompatibleClassMappingException($this->directoryClass, Directory::class);
        }
    }

    /**
     * Set the disk for file interactions.
     */
    public function setDisk(string $disk): AttachmentManager
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Return all directories under a given path.
     *
     * @param  string|null  $path  Use `null` for root of disk.
     */
    public function directories(?string $path = null): Collection
    {
        $directories = array_map(
            fn ($directory) => new $this->directoryClass($directory),
            $this->getFilesystem()->directories($path)
        );

        return Collection::make($directories);
    }

    protected function getFilesystem(): Filesystem
    {
        return Storage::disk($this->disk);
    }

    /**
     * Return files under a given path.
     *
     * @param  string|null  $path  Use `null` for root of disk.
     */
    public function files(?string $path): Collection
    {
        return $this->attachmentClass::whereDisk($this->disk)->wherePath($path)->get();
    }

    /**
     * Upload a file to the selected disk under the desired path and create a database entry.
     *
     * @param  string|null  $desiredPath  Use `null` for root of disk.
     *
     * @throws DestinationAlreadyExistsException if conflicting file name exists in desired path.
     * @throws DisallowedCharacterException if file name contains disallowed characters.
     */
    public function upload(UploadedFile $file, ?string $desiredPath): Attachment
    {
        $filename = new Filename($file);

        $this->validateBasename($filename);

        $path = "{$desiredPath}/{$filename}";
        $disk = $this->getFilesystem();

        if ($disk->exists($path)) {
            throw new DestinationAlreadyExistsException();
        }

        $disk->put($path, $file->getContent());

        return $this->attachmentClass::create([
            'name' => $filename->name,
            'extension' => $filename->extension,
            'mime_type' => $file->getMimeType(),
            'disk' => $this->disk,
            'path' => $desiredPath,
            'size' => $file->getSize(),
        ]);
    }

    /**
     * Validate filename and throw exception if validation fails.
     *
     * @throws DisallowedCharacterException if file name contains disallowed characters.
     */
    public function validateBasename(string $name): void
    {
        if (preg_match_all($this->allowedCharacters, $name)) {
            throw new DisallowedCharacterException();
        }
    }

    /**
     * Rename file on disk and database.
     *
     * @throws DestinationAlreadyExistsException if file in same path exists with conflicting name.
     * @throws DisallowedCharacterException if file name contains disallowed characters.
     */
    public function rename(Attachment $file, string $name): void
    {
        $this->validateBasename($name);

        $disk = $this->getFilesystem();
        $path = "{$file->path}/{$name}.{$file->extension}";

        if ($disk->exists($path)) {
            throw new DestinationAlreadyExistsException();
        }

        $disk->move($file->full_path, $path);

        $file->update(['name' => $name]);

        $file->save();
    }

    /**
     * Move file on disk and database.
     *
     * @throws DestinationAlreadyExistsException if conflicting file exists in desired path.
     */
    public function move(Attachment $file, string $desiredPath): void
    {
        $disk = $this->getFilesystem();
        $path = "{$desiredPath}/{$file->filename}";

        if ($disk->exists($path)) {
            throw new DestinationAlreadyExistsException();
        }

        $disk->move($file->full_path, $path);

        $file->update(['path' => $desiredPath]);

        $file->save();
    }

    /**
     * Rename directory on disk and update children on disk and database.
     *
     * @throws DestinationAlreadyExistsException if conflicting directory name exists.
     * @throws DisallowedCharacterException if directory contains disallowed characters.
     */
    public function renameDirectory(string $currentPath, string $newName): Directory
    {
        $this->validateBasename($newName);

        // Replace old directory name with new directory name.
        $newPath = explode('/', $currentPath);
        $newPath[array_key_last($newPath)] = $newName;
        $newPath = implode('/', $newPath);

        $disk = $this->getFilesystem();
        if ($disk->exists($newPath)) {
            throw new DestinationAlreadyExistsException();
        }

        $disk->move($currentPath, $newPath);

        $attachments = $this->attachmentClass::whereDisk($this->disk)->whereInPath($currentPath)->get();
        foreach ($attachments as $attachment) {
            $attachment->update(['path' => str_replace($currentPath, $newPath, $attachment->path)]);
        }

        return new Directory($newPath);
    }

    /**
     * Create a directory under a specified path.
     *
     * @throws DestinationAlreadyExistsException if conflicting directory name exists.
     * @throws DisallowedCharacterException if directory contains disallowed characters.
     * @throws NoParentDirectoryException if there isn't an existing parent directory
     *                                    when not using the DirectoryStrategies::CREATE_PARENT_DIRECTORIES flag.
     */
    public function createDirectory(string $path, DirectoryStrategies ...$flags): Directory
    {
        $this->validatePath($path);

        $disk = $this->getFilesystem();
        if ($disk->exists($path)) {
            throw new DestinationAlreadyExistsException();
        }

        $createParentDirectoriesFlag = in_array(DirectoryStrategies::CREATE_PARENT_DIRECTORIES, $flags);
        $hasParentDirectory = $disk->exists(dirname($path));

        if (! $createParentDirectoriesFlag && ! $hasParentDirectory) {
            throw new NoParentDirectoryException();
        }

        $disk->makeDirectory($path);

        return new Directory($path);
    }

    /**
     * Validate complete path and throw exception if validation fails.
     *
     * @throws DisallowedCharacterException if path contains disallowed characters.
     */
    protected function validatePath(string $path): void
    {
        foreach (explode('/', $path) as $directory) {
            $this->validateBasename($directory);
        }
    }

    /**
     * Delete directory and remove all files/directory recursively.
     */
    public function deleteDirectory(string $path): void
    {
        $this->attachmentClass::whereDisk($this->disk)->whereInPath($path)->delete();

        $this->getFilesystem()->deleteDirectory($path);
    }

    /**
     * Remove a file from disk and database.
     */
    public function delete(Attachment $file): void
    {
        $this->getFilesystem()->delete($file->full_path);

        $file->delete();
    }

    /**
     * Check if the given path exists on the disk.
     *
     * Examples:
     * - A path like: 'foo/bar'
     * - A file like: 'foo/bar/foobar.jpg'
     */
    public function destinationExists(string $path): bool
    {
        return $this->getFilesystem()->exists($path);
    }

    /**
     * Return full url to attachment.
     */
    public function getUrl(Attachment $file): string|bool
    {
        return route('attachment', ['attachment' => $file->full_path]);
    }

    public function getAbsolutePath(Attachment $file): string
    {
        return Storage::disk($file->disk)->path($file->full_path);
    }

    /**
     * Check if attachment is of given type.
     */
    public function isType(Attachment $file, string $type): bool
    {
        return in_array($file->mime_type, $this->attachmentTypeMapping[$type] ?? []);
    }

    /**
     * Return type of attachment.
     */
    public function getType(Attachment $file): ?string
    {
        foreach ($this->attachmentTypeMapping as $key => $value) {
            if (in_array($file->mime_type, $value)) {
                return $key;
            }
        }

        return null;
    }

    public function getContent(Attachment $file): ?string
    {
        return Storage::disk($this->disk)->get($file->full_path);
    }
}
