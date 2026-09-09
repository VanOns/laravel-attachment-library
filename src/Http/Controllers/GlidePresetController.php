<?php

namespace VanOns\LaravelAttachmentLibrary\Http\Controllers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Filesystem\FilesystemException;
use League\Glide\Server;
use Illuminate\Support\Facades\Validator;
use Throwable;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Glide\FitPresets;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class GlidePresetController
{
    public function __invoke(Request $request, string $preset, string $breakpoint, string $format, string $fit, string $path)
    {
        $validator = Validator::make($request->route()->parameters(), [
            'preset' => ['required', Rule::in(array_keys(config('glide.presets')))],
            'format' => ['required', Rule::in(config('glide.formats'))],
            'breakpoint' => ['required', 'numeric', Rule::in(config('glide.breakpoints'))],
            'fit' => ['required', function (string $attribute, mixed $value, Closure $fail) {
                if (!is_string($value) || !FitPresets::isValidSegment($value)) {
                    $fail("The {$attribute} is not a valid fit preset.");
                }
            }],
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
        $resolvedFit = FitPresets::resolve($fit, $attachment);

        $server = app(Server::class);

        // Cache under the requested fit, so the URL matches the path the webserver serves from.
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
            [ ...$options, 'fm' => $format, 'fit' => $resolvedFit ]
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
}
