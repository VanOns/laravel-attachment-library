<?php

use VanOns\LaravelAttachmentLibrary\Models\Attachment;

it('keeps the other constraints when filtering in a path', function () {
    $inPath = Attachment::factory()->create(['disk' => 'a', 'path' => 'photos']);
    $inSubdirectory = Attachment::factory()->create(['disk' => 'a', 'path' => 'photos/2026']);
    Attachment::factory()->create(['disk' => 'a', 'path' => 'videos']);
    Attachment::factory()->create(['disk' => 'b', 'path' => 'photos']);
    Attachment::factory()->create(['disk' => 'b', 'path' => 'photos/2026']);

    $ids = Attachment::whereDisk('a')->whereInPath('photos')->pluck('id')->sort()->values()->all();

    expect($ids)->toBe([$inPath->id, $inSubdirectory->id]);
});
