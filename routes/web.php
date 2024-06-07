<?php

use Illuminate\Support\Facades\Route;
use VanOns\LaravelAttachmentLibrary\Glide\GlideController;
use VanOns\LaravelAttachmentLibrary\Http\Controllers\AttachmentController;

Route::get('files/{attachment}', AttachmentController::class)
    ->where('attachment', '.*')
    ->middleware(['web'])
    ->name('attachment');

Route::get('img/{path}', GlideController::class)
    ->where('path', '.*')
    ->name('glide');
