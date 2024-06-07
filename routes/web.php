<?php

use Illuminate\Support\Facades\Route;
use VanOns\LaravelAttachmentLibrary\Http\Controllers\AttachmentController;

Route::get('files/{attachment}', AttachmentController::class)
    ->where('attachment', '.*')
    ->middleware(['web'])
    ->name('attachment');
