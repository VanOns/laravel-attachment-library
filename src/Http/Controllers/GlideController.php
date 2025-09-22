<?php

namespace VanOns\LaravelAttachmentLibrary\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Middleware\ValidateSignature;
use Intervention\Image\Exception\NotReadableException;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\Response;
use VanOns\LaravelAttachmentLibrary\Glide\GlideManager;
use VanOns\LaravelAttachmentLibrary\Glide\OptionsParser;
use VanOns\LaravelAttachmentLibrary\Glide\Resizer;

class GlideController implements HasMiddleware
{
    /**
     * Return image response with Glide parameters.
     *
     * @see Resizer for all available Glide parameters.
     */
    public function __invoke(Request $request, string $options, string $path, OptionsParser $parser): Response
    {
        try {
            return app(Server::class)->getImageResponse(
                $path,
                $parser->toArray($options)
            );
        } catch (FileNotFoundException) {
            abort(404);
        } catch (NotReadableException) {
            $file = config('glide.source') . "/{$path}";

            if (!file_exists($file)) {
                abort(404);
            }

            return response()->file(
                $file
            );
        }
    }

    /**
     * All requests to this controller must contain a valid signature.
     */
    public static function middleware(): array
    {
        return [ValidateSignature::class];
    }
}
