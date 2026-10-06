<?php

namespace VanOns\LaravelAttachmentLibrary\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use VanOns\LaravelAttachmentLibrary\Enums\AttachmentType;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Facades\Ffmpeg;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class ProcessVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attachment-library:process-videos
        {--posters : Also generate a poster for videos without one}
        {--force : Process every video, including those that were processed before}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store the dimensions and duration of existing videos, and optionally generate posters';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! Ffmpeg::isAvailable()) {
            $this->error('ffmpeg and ffprobe are not available. Install them or configure their paths in attachment-library.ffmpeg.');

            return self::FAILURE;
        }

        $posters = (bool) $this->option('posters');

        $query = Attachment::whereType(AttachmentType::PREVIEWABLE_VIDEO)
            ->unless($this->option('force'), fn (Builder $query) => $query->where(
                fn (Builder $query) => $query->whereNull('width')->when($posters, fn (Builder $query) => $query->orWhereNull('poster_id'))
            ));

        $bar = $this->output->createProgressBar($query->count());

        $query->lazyById()->each(function (Attachment $video) use ($bar, $posters) {
            AttachmentManager::processVideo($video, $posters);
            $bar->advance();
        });

        $bar->finish();
        $this->newLine();
        $this->info('Videos processed successfully.');

        return self::SUCCESS;
    }
}
