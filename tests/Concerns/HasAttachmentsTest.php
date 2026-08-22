<?php

namespace VanOns\LaravelAttachmentLibrary\Test\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;
use VanOns\LaravelAttachmentLibrary\Test\Fixtures\Post;
use VanOns\LaravelAttachmentLibrary\Test\TestCase;

class HasAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    public function testAttachmentsAreReturnedInPivotOrder()
    {
        $post = Post::create(['title' => 'Hello world']);
        $first = Attachment::factory()->create();
        $second = Attachment::factory()->create();

        $post->attachments()->attach($second->id, ['order' => 0]);
        $post->attachments()->attach($first->id, ['order' => 1]);

        $ids = $post->attachments()->pluck('attachments.id')->all();

        $this->assertSame([$second->id, $first->id], $ids);
    }

    public function testAttachmentCollectionFiltersByPivotCollection()
    {
        $post = Post::create(['title' => 'Hello world']);
        $hero = Attachment::factory()->create();
        $gallery = Attachment::factory()->create();

        $post->attachments()->attach($hero->id, ['collection' => 'hero']);
        $post->attachments()->attach($gallery->id, ['collection' => 'gallery']);

        $collection = $post->attachmentCollection('gallery')->get();

        $this->assertCount(1, $collection);
        $this->assertSame($gallery->id, $collection->first()->id);
    }

    protected function afterRefreshingDatabase(): void
    {
        $migrations = [
            require(__DIR__ . '/../../database/migrations/create_attachments_table.php.stub'),
            require(__DIR__ . '/../../database/migrations/create_attachables_table.php.stub'),
            require(__DIR__ . '/../../database/migrations/add_collection_to_attachables_table.php.stub'),
            require(__DIR__ . '/../../database/migrations/add_order_to_attachables_table.php.stub'),
        ];

        foreach ($migrations as $migration) {
            $migration->up();
        }

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });
    }
}
