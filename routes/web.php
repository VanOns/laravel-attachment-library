<?php

use Illuminate\Support\Facades\Route;
use VanOns\LaravelAttachmentLibrary\Http\Controllers\AttachmentController;
use VanOns\LaravelAttachmentLibrary\Http\Controllers\GlideController;
use VanOns\LaravelAttachmentLibrary\Http\Controllers\GlidePresetController;

Route::get('files/{attachment}', AttachmentController::class)
    ->where('attachment', '.*')
    ->middleware(['web'])
    ->name('attachment');

Route::get('img/{preset}/{breakpoint}/{format}/{fit}/{path}', GlidePresetController::class)
    ->whereIn('preset', array_keys(config('glide.presets', [])))
    ->where('path', '.*')
    ->middleware(['web'])
    ->name('glide.preset');

Route::get('img/{options}/{path}', GlideController::class)
    ->where('path', '.*')
    ->middleware(['web'])
    ->name('glide');
