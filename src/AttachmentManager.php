<?php

namespace VanOns\LaravelAttachmentLibrary;

use VanOns\LaravelAttachmentLibrary\Models\Attachment;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Performs attachment related actions on database and filesystem
 */
class AttachmentManager
{
    protected string $disk;
    protected string $model;

    public function __construct()
    {
        $this->disk = Config::get('attachments.disk', 'public');
        $this->model = Config::get('attachments.model', Attachment::class);
    }

    protected function getFilesystem(): Filesystem
    {
        return Storage::disk($this->disk);
    }

    public function setDisk(string $disk): AttachmentManager
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Retrieves all directories under a given path
     */
    public function directories(?string $path = null): Collection
    {
        return collect($this->getFilesystem()->directories($path));
    }

    /**
     * Retrieve files under a path
     */
    public function files(?string $path): Collection
    {
        return $this->model::whereDisk($this->disk)->wherePath($path)->get();
    }

    /**
     * Uploads a file to the selected disk under the desired path and creates a database entry
     */
    public function upload(UploadedFile $file, ?string $desiredPath): bool
    {
        $path = "{$desiredPath}/{$file->getClientOriginalName()}";
        $disk = $this->getFilesystem();

        if ($disk->exists($path)) return false;

        $disk->put($path, $file->getContent());

        $this->model::create([
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'disk' => $this->disk,
            'path' => $desiredPath
        ]);

        return true;
    }

    /**
     * Removes a file from disk and database
     */
    public function remove(Attachment $file): bool
    {
        $this->getFilesystem()->delete($file->fullPath);

        $file->delete();

        return true;
    }

    /**
     * Rename file on disk and database
     */
    public function rename(Attachment $file, string $name): bool
    {
        $disk = $this->getFilesystem();
        $path = "{$file->path}/{$name}";

        if($disk->exists($path)) return false;

        $disk->move($file->fullPath, $path);

        $file->update(['name' => $name]);

        $file->save();

        return true;
    }

    /**
     * Rename directory on disk and update children on disk and database
     */
    public function renameDirectory(string $oldPath, string $newPath): bool
    {
        $disk = $this->getFilesystem();

        if($disk->exists($newPath)) return false;

        $disk->move($oldPath, $newPath);

        $filesInDirectory = $this->model::whereDisk($this->disk)->whereLikePath("{$oldPath}%")->get();

        foreach ($filesInDirectory as $file) $file->update(['path' => str_replace($oldPath, $newPath, $file->path)]);

        return true;
    }

    /**
     * Create a directory under a specified path
     */
    public function createDirectory(?string $path): bool
    {
        $disk = $this->getFilesystem();

        if($disk->exists($path)) return false;

        return $disk->makeDirectory($path);
    }

    /**
     * Delete directory and remove all files/directory recursively
     */
    public function deleteDirectory(?string $path): bool
    {
        $filesInDirectory = $this->model::whereDisk($this->disk)->whereLikePath("{$path}%")->get();

        foreach ($filesInDirectory as $file) $file->delete();

        $this->getFilesystem()->deleteDirectory($path);

        return true;
    }

    public function getUrl(Attachment $file): string|bool
    {
        return Storage::disk($file->disk)->url($file->full_path);
    }
}
