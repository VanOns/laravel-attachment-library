<?php

namespace VanOns\LaravelAttachmentLibrary\Glide;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Middleware\ValidateSignature;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\Response;

class GlideController implements HasMiddleware
{
    public function __invoke(Request $request): Response
    {
        return app(Server::class)->getImageResponse($request->path(), $request->all());
    }

    public static function middleware(): array
    {
        return [ValidateSignature::class];
    }
}
