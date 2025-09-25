<?php

namespace VanOns\LaravelAttachmentLibrary\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Middleware\ValidateSignature;
use Intervention\Image\Exception\NotReadableException;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Filesystem\FilesystemException;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\Response;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Glide\OptionsParser;
use VanOns\LaravelAttachmentLibrary\Glide\Resizer;

class GlideController implements HasMiddleware
{
    /**
     * Return image response with Glide parameters.
     *
     * @throws FilesystemException
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
            $attachment = AttachmentManager::file($path);
            if (!$attachment) {
                abort(404);
            }

            // Return the original file if Glide cannot parse the image.
            return response()->file($attachment->absolute_path);
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
