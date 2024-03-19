<?php

namespace VanOns\LaravelAttachmentLibrary;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use VanOns\LaravelAttachmentLibrary\Exceptions\DestinationAlreadyExistsException;
use VanOns\LaravelAttachmentLibrary\Exceptions\DisallowedCharacterException;
use VanOns\LaravelAttachmentLibrary\Exceptions\IncompatibleModelConfigurationException;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

/**
 * Performs attachment related actions on database and filesystem.
 */
class AttachmentManager
{
    protected string $disk;

    protected string $model;

    /**
     * @throws IncompatibleModelConfigurationException
     */
    public function __construct()
    {
        $this->disk = Config::get('attachments.disk', 'public');
        $this->model = Config::get('attachments.model', Attachment::class);

        $this->ensureCompatibleModel();
    }

    /**
     * Throw exception if configured model is not a instance of the Attachment model.
     *
     * @throws IncompatibleModelConfigurationException
     */
    protected function ensureCompatibleModel(): void
    {
        $instanceOfAttachment = (new $this->model) instanceof Attachment;

        if (! $instanceOfAttachment) {
            throw new IncompatibleModelConfigurationException();
        }
    }

    /**
     * Validate filename and throw exception if validation fails.
     *
     * @throws DisallowedCharacterException if file name contains disallowed characters.
     */
    protected function validateFilename(string $name): void
    {
        $characters = Config::get('attachments.allowed_characters');

        if (preg_match($characters, $name)) {
            throw new DisallowedCharacterException();
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
     * @param  ?string  $path  Use NULL for root of disk.
     */
    public function directories(?string $path = null): Collection
    {
        return collect($this->getFilesystem()->directories($path));
    }

    protected function getFilesystem(): Filesystem
    {
        return Storage::disk($this->disk);
    }

    /**
     * Return files under a given path.
     *
     * @param  ?string  $path  Use NULL for root of disk.
     */
    public function files(?string $path): Collection
    {
        return $this->model::whereDisk($this->disk)->wherePath($path)->get();
    }

    /**
     * Uploads a file to the selected disk under the desired path and creates a database entry.
     *
     * @param  ?string  $desiredPath  Use NULL for root of disk.
     *
     * @throws DestinationAlreadyExistsException if conflicting file name exists in desired path.
     */
    public function upload(UploadedFile $file, ?string $desiredPath): Attachment
    {
        $path = "{$desiredPath}/{$file->getClientOriginalName()}";
        $disk = $this->getFilesystem();

        if ($disk->exists($path)) {
            throw new DestinationAlreadyExistsException();
        }

        $disk->put($path, $file->getContent());

        return $this->model::create([
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'disk' => $this->disk,
            'path' => $desiredPath,
        ]);
    }

    /**
     * Rename file on disk and database.
     *
     * @throws DestinationAlreadyExistsException if file in same path exists with conflicting name.
     */
    public function rename(Attachment $file, string $name): void
    {
        $this->validateFilename($name);

        $disk = $this->getFilesystem();
        $path = "{$file->path}/{$name}";

        if ($disk->exists($path)) {
            throw new DestinationAlreadyExistsException();
        }

        $disk->move($file->fullPath, $path);

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
        $path = "{$desiredPath}/{$file->name}";

        if ($disk->exists($path)) {
            throw new DestinationAlreadyExistsException();
        }

        $disk->move($file->fullPath, $path);

        $file->update(['path' => $desiredPath]);

        $file->save();
    }

    /**
     * Rename directory on disk and update children on disk and database.
     *
     * @throws DestinationAlreadyExistsException if conflicting directory name exists.
     */
    public function renameDirectory(string $oldPath, string $newPath): void
    {
        $disk = $this->getFilesystem();

        if ($disk->exists($newPath)) {
            throw new DestinationAlreadyExistsException();
        }

        $disk->move($oldPath, $newPath);

        $filesInDirectory = $this->model::whereDisk($this->disk)->whereInPath($oldPath)->get();

        foreach ($filesInDirectory as $file) {
            $file->update(['path' => str_replace($oldPath, $newPath, $file->path)]);
        }
    }

    /**
     * Create a directory under a specified path.
     *
     * @throws DestinationAlreadyExistsException if conflicting directory name exists.
     */
    public function createDirectory(string $path): void
    {
        $disk = $this->getFilesystem();

        if ($disk->exists($path)) {
            throw new DestinationAlreadyExistsException();
        }

        $disk->makeDirectory($path);
    }

    /**
     * Delete directory and remove all files/directory recursively.
     */
    public function deleteDirectory(string $path): void
    {
        $this->model::whereDisk($this->disk)->whereInPath($path)->delete();

        $this->getFilesystem()->deleteDirectory($path);
    }

    /**
     * Removes a file from disk and database.
     */
    public function delete(Attachment $file): void
    {
        $this->getFilesystem()->delete($file->fullPath);

        $file->delete();
    }

    /**
     * Checks if a path exists on the disk.
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
        return Storage::disk($file->disk)->url($file->fullPath);
    }
}
