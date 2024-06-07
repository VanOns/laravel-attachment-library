<?php

use Illuminate\Support\Facades\Route;
use VanOns\LaravelAttachmentLibrary\Glide\GlideController;

Route::get('img/{path}', GlideController::class)
    ->where('path', '.*')
    ->name('glide');
