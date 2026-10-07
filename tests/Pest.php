<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use VanOns\LaravelAttachmentLibrary\Facades\Ffmpeg;

uses(VanOns\LaravelAttachmentLibrary\Test\TestCase::class)->in(__DIR__);

/**
 * Fake the ffmpeg and ffprobe binaries. Frame extraction writes a real JPEG to the target path.
 */
function fakeFfmpeg(?array $stream = ['width' => 1920, 'height' => 1080], string $duration = '12.5'): void
{
    resetFfmpeg();

    Process::fake(function (PendingProcess $process) use ($stream, $duration) {
        $command = $process->command;

        if (in_array('-version', $command)) {
            return Process::result('version');
        }

        if ($command[0] === 'ffprobe') {
            return Process::result(json_encode([
                'streams' => $stream ? [$stream] : [],
                'format' => ['duration' => $duration],
            ]));
        }

        $frame = UploadedFile::fake()->image('frame.jpg', 16, 9);
        copy($frame->getRealPath(), end($command));

        return Process::result();
    });
}

function fakeMissingFfmpeg(): void
{
    resetFfmpeg();

    Process::fake(fn () => Process::result(errorOutput: 'command not found', exitCode: 127));
}

/**
 * Forget the memoised availability check, so a new fake takes effect within the same test.
 */
function resetFfmpeg(): void
{
    app()->forgetInstance('attachment.ffmpeg');
    Ffmpeg::clearResolvedInstance('attachment.ffmpeg');
}
