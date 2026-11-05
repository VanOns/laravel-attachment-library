<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;
use VanOns\LaravelAttachmentLibrary\Test\Fixtures\CustomAttachment;
use VanOns\LaravelAttachmentLibrary\Test\Fixtures\Post;

beforeEach(function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->timestamps();
    });
});

it('returns attachments in pivot order', function () {
    $post = Post::create(['title' => 'Hello world']);
    $first = Attachment::factory()->create();
    $second = Attachment::factory()->create();

    $post->attachments()->attach($second->id, ['order' => 0]);
    $post->attachments()->attach($first->id, ['order' => 1]);

    $ids = $post->attachments()->pluck('attachments.id')->all();

    expect($ids)->toBe([$second->id, $first->id]);
});

it('filters the attachment collection by pivot collection', function () {
    $post = Post::create(['title' => 'Hello world']);
    $hero = Attachment::factory()->create();
    $gallery = Attachment::factory()->create();

    $post->attachments()->attach($hero->id, ['collection' => 'hero']);
    $post->attachments()->attach($gallery->id, ['collection' => 'gallery']);

    $collection = $post->attachmentCollection('gallery')->get();

    expect($collection)->toHaveCount(1);
    expect($collection->first()->id)->toBe($gallery->id);
});

it('relates the attachment class from the class mapping', function () {
    Config::set('attachment-library.class_mapping.attachment', CustomAttachment::class);
    $post = Post::create(['title' => 'Hello world']);
    $post->attachments()->attach(Attachment::factory()->create()->id);

    expect($post->attachments->first())->toBeInstanceOf(CustomAttachment::class);
});

it('still relates the attachment class from the old model key', function () {
    Config::set('attachment-library.model', CustomAttachment::class);
    $post = Post::create(['title' => 'Hello world']);
    $post->attachments()->attach(Attachment::factory()->create()->id);

    expect($post->attachments->first())->toBeInstanceOf(CustomAttachment::class);
});

it('finds the models an attachment of the mapped class belongs to', function () {
    Config::set('attachment-library.class_mapping.attachment', CustomAttachment::class);
    $post = Post::create(['title' => 'Hello world']);
    $attachment = Attachment::factory()->create();
    $post->attachments()->attach($attachment->id);

    expect(CustomAttachment::find($attachment->id)->related(Post::class)->pluck('posts.id')->all())->toBe([$post->id]);
});
