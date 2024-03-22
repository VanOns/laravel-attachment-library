<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'disk' => 'test',
            'mime_type' => fake()->mimeType(),
            'path' => null,
        ];
    }
}
