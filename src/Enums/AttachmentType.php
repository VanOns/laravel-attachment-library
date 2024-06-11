<?php

namespace VanOns\LaravelAttachmentLibrary\Enums;

class AttachmentType
{
    public const PREVIEWABLE_IMAGE = 'PREVIEWABLE_IMAGE';

    public const PREVIEWABLE_VIDEO = 'PREVIEWABLE_VIDEO';

    public const PREVIEWABLE_AUDIO = 'PREVIEWABLE_AUDIO';

    public const PREVIEWABLE_DOCUMENT = 'PREVIEWABLE_DOCUMENT';

    public static function isRenderable(?string $type): bool
    {
        if ($type === null) {
            return false;
        }

        return in_array($type, [
            self::PREVIEWABLE_IMAGE,
            self::PREVIEWABLE_VIDEO,
            self::PREVIEWABLE_AUDIO,
            self::PREVIEWABLE_DOCUMENT,
        ]);
    }
}
