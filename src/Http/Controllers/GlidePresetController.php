<?php

namespace VanOns\LaravelAttachmentLibrary\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Filesystem\FilesystemException;
use League\Glide\Server;
use Throwable;
use Validator;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class GlidePresetController
{
    public function __invoke(Request $request, string $preset, string $breakpoint, string $format, string $fit, string $path)
    {
        $validator = Validator::make($request->route()->parameters(), [
            'preset' => ['required', Rule::in(array_keys(config('glide.presets')))],
            'format' => ['required', Rule::in(config('glide.formats'))],
            'breakpoint' => ['required', 'numeric', Rule::in(config('glide.breakpoints'))],
        ]);

        if ($validator->fails()) {
            abort(403);
        }

        try {
            return $this->generateImage($preset, $breakpoint, $format, $fit, $path);
        } catch (FileNotFoundException) {
            abort(404);
        } catch (Throwable) {
            $attachment = AttachmentManager::file($path);
            if (!$attachment) {
                abort(404);
            }

            // Return the original file if Glide cannot parse the image.
            return response()->file($attachment->absolute_path);
        }
    }

    /**
     * @throws FilesystemException
     * @throws FileNotFoundException
     */
    private function generateImage(string $preset, string $breakpoint, string $format, string $fit, string $path)
    {
        $attachment = $this->getAttachment($path);
        $fit = $this->getFit($attachment);

        $server = app(Server::class);

        $server->setCachePathCallable(function () use ($preset, $breakpoint, $format, $fit, $path) {
            return "{$preset}/{$breakpoint}/{$format}/{$fit}/{$path}";
        });

        $options = config("glide.presets.{$preset}");
        $widthScale = $options['w'] ?? null;
        $heightScale = $options['h'] ?? null;
        unset($options['w'], $options['h']);

        if ($widthScale) {
            $options['w'] = $widthScale * (int)$breakpoint;
        }

        if ($heightScale) {
            $options['h'] = $heightScale * (int)$breakpoint;
        }

        return $server->getImageResponse(
            $path,
            [ ...$options, 'fm' => $format, 'fit' => $fit ]
        );
    }

    private function getAttachment(string $path): ?Attachment
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $dir = $dir === '.' ? null : $dir;
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return Attachment::where('path', $dir)
            ->where('name', $filename)
            ->where('extension', $extension)
            ->first();
    }

    private function getFit(?Attachment $attachment)
    {
        if (!$attachment) {
            return 'crop';
        }

        if ($attachment->focal_point) {
            $x = $attachment->focal_point['x'] ?? 50;
            $y = $attachment->focal_point['y'] ?? 50;
            return "crop-{$x}-{$y}";
        }

        return 'crop';
    }
}
