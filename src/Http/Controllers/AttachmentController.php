<?php

namespace VanOns\LaravelAttachmentLibrary\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use VanOns\LaravelAttachmentLibrary\Http\Middleware\EnsureRenderableAttachment;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class AttachmentController implements HasMiddleware
{
    public function __invoke(Request $request, Attachment $attachment): Response
    {
        return response(Storage::disk($attachment->disk)->get($attachment->full_path), headers: [
            'Content-Type' => $attachment->mime_type,
        ]);
    }

    public static function middleware(): array
    {
        return [EnsureRenderableAttachment::class];
    }
}
