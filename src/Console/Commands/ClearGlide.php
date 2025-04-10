<?php

namespace VanOns\LaravelAttachmentLibrary\Console\Commands;

use Illuminate\Console\Command;
use VanOns\LaravelAttachmentLibrary\Facades\Glide;

class ClearGlide extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'glide:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the Glide image cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = Glide::cacheDisk();

        $this->info('Clearing Glide cache...');

        /**
         * Delete all files and then all directories, this ensures compatibility with Flysystem adapters.
         */
        $files = $disk->allFiles();
        $disk->delete($files);

        foreach ($disk->directories() as $directory) {
            $disk->deleteDirectory($directory);
        }

        $this->info('Glide cache cleared successfully.');
    }
}
