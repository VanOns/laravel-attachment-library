<?php

use Illuminate\Support\Facades\Config;
use VanOns\LaravelAttachmentLibrary\Enums\Fit;
use VanOns\LaravelAttachmentLibrary\Glide\FitPresets;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

it('resolves a configured preset', function () {
    Config::set('glide.fit_presets', ['contain' => Fit::CONTAIN->value]);

    expect(FitPresets::resolve('contain'))->toBe('contain');
});

it('resolves an unknown segment to crop', function () {
    expect(FitPresets::resolve('crop-40-60'))->toBe('crop');
    expect(FitPresets::resolve('nonexistent'))->toBe('crop');
});

it('resolves a preset with an invalid fit to crop', function () {
    Config::set('glide.fit_presets', ['broken' => 'not-a-fit']);

    expect(FitPresets::resolve('broken'))->toBe('crop');
});

it('lets the focal point take priority over the crop preset', function () {
    $attachment = new Attachment(['focal_point' => ['x' => 25, 'y' => 75]]);

    expect(FitPresets::resolve('crop', $attachment))->toBe('crop-25-75');
    expect(FitPresets::resolve('crop-40-60', $attachment))->toBe('crop-25-75');
});

it('defaults the focal point to center', function () {
    $attachment = new Attachment(['focal_point' => ['x' => 25]]);

    expect(FitPresets::resolve('crop', $attachment))->toBe('crop-25-50');
});

it('ignores the focal point for a non-crop preset', function () {
    Config::set('glide.fit_presets', ['contain' => Fit::CONTAIN->value]);

    $attachment = new Attachment(['focal_point' => ['x' => 25, 'y' => 75]]);

    expect(FitPresets::resolve('contain', $attachment))->toBe('contain');
});

it('resolves to crop for an attachment without a focal point', function () {
    expect(FitPresets::resolve('crop', new Attachment()))->toBe('crop');
});

it('validates segments', function () {
    expect(FitPresets::isValidSegment('crop'))->toBeTrue();
    expect(FitPresets::isValidSegment('crop-50-50'))->toBeTrue();
    expect(FitPresets::isValidSegment('crop-0-100'))->toBeTrue();

    expect(FitPresets::isValidSegment('contain'))->toBeFalse();
    expect(FitPresets::isValidSegment('crop-1-2-3'))->toBeFalse();
    expect(FitPresets::isValidSegment('crop-1000-1000'))->toBeFalse();
    expect(FitPresets::isValidSegment('../../etc'))->toBeFalse();
    expect(FitPresets::isValidSegment(''))->toBeFalse();
});
