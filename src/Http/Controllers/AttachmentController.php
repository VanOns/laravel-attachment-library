<?php

namespace VanOns\LaravelAttachmentLibrary\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Symfony\Component\HttpFoundation\Response;
use VanOns\LaravelAttachmentLibrary\Http\Middleware\EnsureRenderableAttachment;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class AttachmentController implements HasMiddleware
{
    public function __invoke(Request $request, Attachment $attachment): Response
    {
        return response()->file($attachment->absolute_path);
    }

    public static function middleware(): array
    {
        return [EnsureRenderableAttachment::class];
    }
}
