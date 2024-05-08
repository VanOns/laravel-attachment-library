<?php

namespace VanOns\LaravelAttachmentLibrary\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\Builder;
use VanOns\LaravelAttachmentLibrary\AttachmentQueryBuilder;
use VanOns\LaravelAttachmentLibrary\Database\Factories\AttachmentFactory;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;

/**
 * @property string $name
 * @property string $extension
 * @property string $mime_type
 * @property string $disk
 * @property string $path
 * @property string $full_path
 * @property string $filename
 * @property int $size
 *
 * @mixin AttachmentQueryBuilder
 */
class Attachment extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'extension', 'mime_type', 'disk', 'path', 'size'];

    protected static function newFactory(): Factory
    {
        return AttachmentFactory::new();
    }

    /**
     * Return associated models of given class.
     */
    public function related(string $class): MorphToMany
    {
        return $this->morphedByMany($class, 'attachable');
    }

    /**
     * Check if given type matches attachment type.
     */
    public function isType(string $type): bool
    {
        return AttachmentManager::isType($this, $type);
    }

    /**
     * Return filename including extension.
     */
    public function filename(): Attribute
    {
        return Attribute::make(
            get: fn () => implode('.', [$this->name, $this->extension])
        );
    }

    /**
     * Return path from root of disk including file name and extension.
     */
    public function fullPath(): Attribute
    {
        return Attribute::make(
            get: fn () => implode('/', array_filter([$this->path, $this->filename]))
        );
    }

    /**
     * Return public url from AttachmentManager, may be temporary if stored in S3.
     */
    public function url(): Attribute
    {
        return Attribute::make(
            get: fn () => AttachmentManager::getUrl($this)
        );
    }

    /**
     * Bind AttachmentQueryBuilder to this model.
     *
     * @param  Builder  $query
     */
    public function newEloquentBuilder($query): AttachmentQueryBuilder
    {
        return new AttachmentQueryBuilder($query);
    }
}
