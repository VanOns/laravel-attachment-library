<?php

namespace VanOns\LaravelAttachmentLibrary\Test\Glide;

use Illuminate\Support\Facades\Config;
use VanOns\LaravelAttachmentLibrary\Enums\Fit;
use VanOns\LaravelAttachmentLibrary\Glide\FitPresets;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;
use VanOns\LaravelAttachmentLibrary\Test\TestCase;

class FitPresetsTest extends TestCase
{
    public function testResolvesConfiguredPreset()
    {
        Config::set('glide.fit_presets', ['contain' => Fit::CONTAIN->value]);

        $this->assertSame('contain', FitPresets::resolve('contain'));
    }

    public function testResolvesUnknownSegmentToCrop()
    {
        $this->assertSame('crop', FitPresets::resolve('crop-40-60'));
        $this->assertSame('crop', FitPresets::resolve('nonexistent'));
    }

    public function testResolvesPresetWithInvalidFitToCrop()
    {
        Config::set('glide.fit_presets', ['broken' => 'not-a-fit']);

        $this->assertSame('crop', FitPresets::resolve('broken'));
    }

    public function testFocalPointTakesPriorityOverCropPreset()
    {
        $attachment = new Attachment(['focal_point' => ['x' => 25, 'y' => 75]]);

        $this->assertSame('crop-25-75', FitPresets::resolve('crop', $attachment));
        $this->assertSame('crop-25-75', FitPresets::resolve('crop-40-60', $attachment));
    }

    public function testFocalPointDefaultsToCenter()
    {
        $attachment = new Attachment(['focal_point' => ['x' => 25]]);

        $this->assertSame('crop-25-50', FitPresets::resolve('crop', $attachment));
    }

    public function testFocalPointIsIgnoredForNonCropPreset()
    {
        Config::set('glide.fit_presets', ['contain' => Fit::CONTAIN->value]);

        $attachment = new Attachment(['focal_point' => ['x' => 25, 'y' => 75]]);

        $this->assertSame('contain', FitPresets::resolve('contain', $attachment));
    }

    public function testAttachmentWithoutFocalPointResolvesToCrop()
    {
        $this->assertSame('crop', FitPresets::resolve('crop', new Attachment()));
    }

    public function testValidatesSegments()
    {
        $this->assertTrue(FitPresets::isValidSegment('crop'));
        $this->assertTrue(FitPresets::isValidSegment('crop-50-50'));
        $this->assertTrue(FitPresets::isValidSegment('crop-0-100'));

        $this->assertFalse(FitPresets::isValidSegment('contain'));
        $this->assertFalse(FitPresets::isValidSegment('crop-1-2-3'));
        $this->assertFalse(FitPresets::isValidSegment('crop-1000-1000'));
        $this->assertFalse(FitPresets::isValidSegment('../../etc'));
        $this->assertFalse(FitPresets::isValidSegment(''));
    }
}
