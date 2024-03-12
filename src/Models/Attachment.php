<?php

namespace VanOns\LaravelAttachmentLibrary\Models;

use Database\Factories\AttachmentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\Builder;
use VanOns\LaravelAttachmentLibrary\AttachmentQueryBuilder;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;

/**
 * @mixin AttachmentQueryBuilder
 */
class Attachment extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'mime_type', 'disk', 'path'];

    protected static function newFactory(): Factory
    {
        return AttachmentFactory::new();
    }

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
     * @param Builder $query
     */
    public function newEloquentBuilder($query): AttachmentQueryBuilder
    {
        return new AttachmentQueryBuilder($query);
    }
}
