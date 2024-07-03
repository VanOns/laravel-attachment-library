<?php

use Illuminate\Support\Facades\Route;
use VanOns\LaravelAttachmentLibrary\Http\Controllers\AttachmentController;
use VanOns\LaravelAttachmentLibrary\Http\Controllers\GlideController;

Route::get('files/{attachment}', AttachmentController::class)
    ->where('attachment', '.*')
    ->middleware(['web'])
    ->name('attachment');

Route::get('img/{attachment}', GlideController::class)
    ->where('attachment', '.*')
    ->middleware(['web'])
    ->name('glide');
