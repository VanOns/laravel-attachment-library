<?php

namespace VanOns\LaravelAttachmentLibrary\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Links a caption track (WebVTT attachment) to a video attachment.
 *
 * @property int $video_id
 * @property int $caption_id
 * @property string $language
 * @property string|null $label
 * @property bool $is_default
 * @property int $order
 */
class AttachmentCaption extends Pivot
{
    protected $table = 'attachment_captions';

    public $incrementing = true;

    protected $casts = [
        'is_default' => 'boolean',
        'order' => 'integer',
    ];
}
