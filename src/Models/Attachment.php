<?php

namespace VanOns\LaravelAttachmentLibrary\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\Builder;
use Symfony\Component\HttpFoundation\Response;
use VanOns\LaravelAttachmentLibrary\AttachmentQueryBuilder;
use VanOns\LaravelAttachmentLibrary\Database\Factories\AttachmentFactory;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\Filename;
use VanOns\LaravelAttachmentLibrary\Enums\AttachmentType;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;

/**
 * @property int $created_by
 * @property int $size
 * @property int $updated_by
 * @property string $absolute_path
 * @property string $alt
 * @property string $caption
 * @property string $description
 * @property string $disk
 * @property string $extension
 * @property string $filename
 * @property string $full_path
 * @property string $mime_type
 * @property string $name
 * @property string $path
 * @property string $url
 * @property string $title
 *
 * @mixin AttachmentQueryBuilder
 */
class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'alt',
        'caption',
        'created_by',
        'description',
        'disk',
        'extension',
        'mime_type',
        'name',
        'path',
        'size',
        'title',
        'updated_by',
    ];

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

    public function isImage(): bool
    {
        return $this->isType(AttachmentType::PREVIEWABLE_IMAGE);
    }

    public function isVideo(): bool
    {
        return $this->isType(AttachmentType::PREVIEWABLE_VIDEO);
    }

    public function isAudio(): bool
    {
        return $this->isType(AttachmentType::PREVIEWABLE_AUDIO);
    }

    public function isDocument(): bool
    {
        return $this->isType(AttachmentType::PREVIEWABLE_DOCUMENT);
    }

    /**
     * Return contents of attachment.
     */
    public function getContents(): ?string
    {
        return AttachmentManager::getContents($this);
    }

    /**
     * Check if attachment is on a remote disk.
     */
    public function isRemote(): bool
    {
        return AttachmentManager::isRemote($this);
    }

    /**
     * Return attachment type.
     */
    public function type(): Attribute
    {
        return Attribute::make(
            get: fn () => AttachmentManager::getType($this)
        );
    }

    /**
     * Return filename including extension.
     */
    public function filename(): Attribute
    {
        return Attribute::make(
            get: fn () => implode('.', array_filter([$this->name, $this->extension]))
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
     * Return path from root of filesystem including filename and extension.
     */
    public function absolutePath(): Attribute
    {
        return Attribute::make(
            get: fn () => AttachmentManager::getAbsolutePath($this)
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
     * Return file metadata.
     */
    public function metadata(): Attribute
    {
        return Attribute::make(
            get: fn () => AttachmentManager::getMetadata($this)
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

    /**
     * Bind controller parameter to retrieve attachment.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return Attachment::whereFilename(new Filename($value))->first() ?? abort(Response::HTTP_NOT_FOUND);
    }
}
