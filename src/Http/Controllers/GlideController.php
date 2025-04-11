<?php

namespace VanOns\LaravelAttachmentLibrary\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Middleware\ValidateSignature;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\Response;
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
        return app(Server::class)->getImageResponse(
            $path,
            $parser->toArray($options)
        );
    }

    /**
     * All requests to this controller must contain a valid signature.
     */
    public static function middleware(): array
    {
        return [ValidateSignature::class];
    }
}
