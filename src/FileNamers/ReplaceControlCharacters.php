<?php

namespace VanOns\LaravelAttachmentLibrary\FileNamers;

use Illuminate\Support\Facades\Config;

class ReplaceControlCharacters extends FileNamer
{
    private array $search;

    private array $replace;

    public function __construct()
    {
        $this->search = Config::get('attachment-library.replace_control_character_mapping.search', []);
        $this->replace = Config::get('attachment-library.replace_control_character_mapping.replace', []);
    }

    public function execute(string $value): string
    {
        return preg_replace($this->search, $this->replace, $value);
    }
}
