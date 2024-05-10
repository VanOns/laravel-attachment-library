<?php

namespace VanOns\LaravelAttachmentLibrary\Commands;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class ImportAttachments
{
    protected $description = 'Import all attachments from disk.';
    protected $signature = 'attachment-library:import {disk}';
    private string $disk;
    private Filesystem $storage;

    public function handle(){
        $this->disk = $this->argument('disk');
        $this->storage = Storage::disk($this->disk);

        $this->importFiles();
    }

    /**
     * Recursive function which imports all files into database.
     */
    private function importFiles(string $path = null)
    {
        foreach ($this->storage->files($path) as $file) {
            $attachment = Attachment::whereDisk($this->disk)
                ->wherePath($path)
                ->where('name', 'LIKE', pathinfo($file, PATHINFO_FILENAME))
                ->where('extension', 'LIKE', pathinfo($file, PATHINFO_EXTENSION))
                ->first();

            if ($attachment !== null) continue;

            Attachment::create([
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'extension' => pathinfo($file, PATHINFO_EXTENSION),
                'mime_type' =>$this->storage->mimeType($file),
                'disk' => $this->disk,
                'path' => $path,
                'size' => $this->storage->size($file),
            ]);
        }

        foreach ($this->storage->directories($path) as $directory) {
            $this->importFiles($directory);
        }
    }
}
