<?php

use Illuminate\Support\Facades\Route;
use VanOns\LaravelAttachmentLibrary\Http\Controllers\AttachmentController;
use VanOns\LaravelAttachmentLibrary\Http\Controllers\GlideController;

Route::get('files/{attachment}', AttachmentController::class)
    ->where('attachment', '.*')
    ->middleware(['web'])
    ->name('attachment');

Route::get('img/{options}/{path}', GlideController::class)
    ->where('path', '.*')
    ->middleware(['web'])
    ->name('glide');
