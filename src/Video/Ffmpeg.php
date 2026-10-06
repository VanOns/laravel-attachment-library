<?php

namespace VanOns\LaravelAttachmentLibrary\Video;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\RuntimeException;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

/**
 * Runs ffmpeg and ffprobe to read video dimensions and extract poster frames.
 */
class Ffmpeg
{
    protected ?bool $available = null;

    /**
     * Check whether both the ffmpeg and ffprobe binaries can be executed.
     */
    public function isAvailable(): bool
    {
        return $this->available ??= $this->run([$this->binary('ffmpeg'), '-version']) !== null
            && $this->run([$this->binary('ffprobe'), '-version']) !== null;
    }

    /**
     * Return the dimensions and duration (in seconds) of the first video stream.
     *
     * @return array{width: int, height: int, duration: float|null}|null
     */
    public function probe(Attachment $video): ?array
    {
        $output = $this->withLocalPath($video, fn (string $path) => $this->run([
            $this->binary('ffprobe'),
            '-v', 'error',
            '-select_streams', 'v:0',
            '-show_entries', 'stream=width,height:format=duration',
            '-of', 'json',
            $path,
        ]));

        $data = json_decode($output ?? '', true);
        $stream = $data['streams'][0] ?? null;

        if (! isset($stream['width'], $stream['height'])) {
            Log::warning('Could not probe video dimensions.', ['attachment' => $video->id]);

            return null;
        }

        return [
            'width' => (int) $stream['width'],
            'height' => (int) $stream['height'],
            'duration' => isset($data['format']['duration']) ? (float) $data['format']['duration'] : null,
        ];
    }

    /**
     * Write the first frame of the video to the given path as a JPEG.
     */
    public function extractFirstFrame(Attachment $video, string $targetPath): bool
    {
        $output = $this->withLocalPath($video, fn (string $path) => $this->run([
            $this->binary('ffmpeg'),
            '-v', 'error',
            '-y',
            '-i', $path,
            '-frames:v', '1',
            '-q:v', '2',
            $targetPath,
        ]));

        if ($output === null || ! is_file($targetPath) || filesize($targetPath) === 0) {
            Log::warning('Could not extract the first frame of a video.', ['attachment' => $video->id]);

            return false;
        }

        return true;
    }

    /**
     * Return the output of the command, or null when it fails.
     */
    protected function run(array $command): ?string
    {
        try {
            $result = Process::timeout(Config::get('attachment-library.ffmpeg.timeout', 60))->run($command);
        } catch (RuntimeException) {
            return null;
        }

        return $result->successful() ? $result->output() : null;
    }

    /**
     * Run the callback with a local path to the video, downloading it first when stored on a remote disk.
     */
    protected function withLocalPath(Attachment $video, Closure $callback): mixed
    {
        if (! $video->isRemote()) {
            return $callback($video->absolute_path);
        }

        $stream = Storage::disk($video->disk)->readStream($video->full_path);

        if (! $stream) {
            return null;
        }

        $tmpFile = tmpfile();
        stream_copy_to_stream($stream, $tmpFile);
        fclose($stream);

        try {
            return $callback(stream_get_meta_data($tmpFile)['uri']);
        } finally {
            fclose($tmpFile);
        }
    }

    protected function binary(string $name): string
    {
        return Config::get("attachment-library.ffmpeg.{$name}_path", $name);
    }
}
