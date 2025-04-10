<?php

namespace VanOns\LaravelAttachmentLibrary\Console\Commands;

use Illuminate\Console\Command;
use VanOns\LaravelAttachmentLibrary\Facades\Glide;

class GlideStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'glide:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get glide cache stats';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $stats = Glide::cacheStats();
        $this->info('Glide cache stats:');
        $this->line('Total files: ' . $stats['files']);
        $this->line('Total size: ' . $stats['readable_size']);
    }
}
