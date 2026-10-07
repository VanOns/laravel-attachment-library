<?php

use VanOns\LaravelAttachmentLibrary\Video\SrtToVtt;

it('converts subrip cues to webvtt', function () {
    $srt = "1\n00:00:01,000 --> 00:00:04,250\nHello\n\n2\n00:01:02,500 --> 00:01:05,000\nWorld\n";

    expect(SrtToVtt::convert($srt))->toBe("WEBVTT\n\n1\n00:00:01.000 --> 00:00:04.250\nHello\n\n2\n00:01:02.500 --> 00:01:05.000\nWorld\n");
});

it('strips the byte order mark and normalises line endings', function () {
    $srt = "\xEF\xBB\xBF1\r\n00:00:01,000 --> 00:00:02,000\r\nHello\r\n";

    expect(SrtToVtt::convert($srt))->toBe("WEBVTT\n\n1\n00:00:01.000 --> 00:00:02.000\nHello\n");
});

it('keeps commas in cue text', function () {
    $srt = "1\n00:00:01,000 --> 00:00:02,000\nWell, hello there\nSecond line, too\n";

    expect(SrtToVtt::convert($srt))->toContain("Well, hello there\nSecond line, too");
});
