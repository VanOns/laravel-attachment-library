<?php

namespace VanOns\LaravelAttachmentLibrary\Glide;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use VanOns\LaravelAttachmentLibrary\Enums\Fit;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

/**
 * Resolves the `{fit}` segment of preset image URLs to a Glide fit parameter.
 */
class FitPresets
{
    /**
     * Focal point crops as emitted by earlier versions of the package.
     */
    private const FOCAL_POINT_PATTERN = '/^crop-\d{1,3}-\d{1,3}$/';

    /**
     * Check whether the given segment may be used in a preset image URL.
     */
    public static function isValidSegment(string $segment): bool
    {
        return array_key_exists($segment, Config::get('glide.fit_presets', []))
            || preg_match(self::FOCAL_POINT_PATTERN, $segment) === 1;
    }

    /**
     * Return the Glide fit parameter for the given segment.
     *
     * The focal point of the attachment takes priority over presets that resolve to `crop`.
     */
    public static function resolve(string $segment, ?Attachment $attachment = null): string
    {
        $preset = Config::get('glide.fit_presets', [])[$segment] ?? null;
        $fit = $preset ?? Fit::CROP->value;

        if (Str::startsWith($fit, 'crop') && $attachment?->focal_point) {
            $x = $attachment->focal_point['x'] ?? 50;
            $y = $attachment->focal_point['y'] ?? 50;

            return "crop-{$x}-{$y}";
        }

        return $fit;
    }
}
