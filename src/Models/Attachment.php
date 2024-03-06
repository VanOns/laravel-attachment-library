<?php

namespace VanOns\LaravelAttachmentLibrary\Models;

use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\AttachmentQueryBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @mixin AttachmentQueryBuilder
 */
class Attachment extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'mime_type', 'disk', 'path'];

    /**
     * Retrieve associated models of given class
     */
    public function related(string $class): MorphToMany
    {
        return $this->morphedByMany($class, 'attachable');
    }

    /**
     * Return full path
     */
    public function fullPath(): Attribute
    {
        return Attribute::make(
            get: fn() => implode('/', array_filter([$this->path, $this->name]))
        );
    }

    /**
     * Return public url, may be temporary if stored in S3
     */
    public function url(): Attribute
    {
        return Attribute::make(
            get: fn() => AttachmentManager::getUrl($this)
        );
    }

    /**
     * Contains custom queries related to Attachment models
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    public function newEloquentBuilder($query): AttachmentQueryBuilder
    {
        return new AttachmentQueryBuilder($query);
    }
}
