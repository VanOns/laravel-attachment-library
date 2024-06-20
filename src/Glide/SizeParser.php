<?php

namespace VanOns\LaravelAttachmentLibrary\Glide;

class SizeParser
{
    public function __construct(public array $breakpoints = []) {}

    public function parse(string $sizes): array
    {
        $fallback = $this->getDefaultSize($sizes);
        $foundSizes = $this->getSizes($sizes);
        return $this->supplementSizes($foundSizes, $fallback);
    }

    public function supplementSizes(array $foundSizes, string $fallback): array
    {
        $output = [];

        foreach(array_keys($this->breakpoints) as $breakpoint) {
            $output[$breakpoint] = $foundSizes[$breakpoint] ?? null;
        }

        $previous = $fallback;
        foreach(array_keys($this->breakpoints) as $breakpoint) {
            if ($output[$breakpoint] === null) {
                $output[$breakpoint] = $previous;
            }

            $previous = $output[$breakpoint];
        }

        $output['default'] = $fallback;

        return $output;
    }

    public function getSizes(string $sizes)
    {
        return array_reduce(explode(' ', $sizes), function ($output, $size) {
            $parts = explode(':', $size);

            if (count($parts) !== 2) {
                return $output;
            }

            if (in_array($parts[0], array_keys($this->breakpoints))) {
                $output[$parts[0]] = $parts[1];
            }

            return $output;
        }, []);
    }

    public function getDefaultSize(string $sizes): string
    {
        return array_reduce(explode(' ', $sizes), function ($output, $size) {
            $parts = explode(':', $size);

            if (count($parts) === 1) {
                return $parts[0];
            }

            return $output;
        }, 'full');
    }
}
