<?php

namespace VanOns\LaravelAttachmentLibrary\Test\Fixtures;

use Illuminate\Database\Eloquent\Model;
use VanOns\LaravelAttachmentLibrary\Concerns\HasAttachments;

class Post extends Model
{
    use HasAttachments;

    protected $fillable = ['title'];
}
