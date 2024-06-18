<?php

namespace VanOns\LaravelAttachmentLibrary\FileNamers;

class ReplaceControlCharacters extends FileNamer
{
    private array $search;

    private array $replace;

    public function __construct()
    {
        $this->search = $this->getConfig('search', []);
        $this->replace = $this->getConfig('replace', []);
    }

    public function execute(string $value): string
    {
        return preg_replace($this->search, $this->replace, $value);
    }
}
