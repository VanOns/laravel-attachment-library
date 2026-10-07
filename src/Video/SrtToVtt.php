<?php

namespace VanOns\LaravelAttachmentLibrary\Video;

/**
 * Converts SubRip (.srt) subtitles to WebVTT, the only caption format browsers play.
 */
class SrtToVtt
{
    public static function convert(string $srt): string
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $srt);
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = preg_replace('/(\d{2}:\d{2}:\d{2}),(\d{3})/', '$1.$2', $content);

        return "WEBVTT\n\n" . trim($content) . "\n";
    }
}
