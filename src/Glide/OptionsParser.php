<?php

namespace VanOns\LaravelAttachmentLibrary\Glide;

class OptionsParser
{
    public function toArray(string $options): array
    {
        return collect(explode(',', $options))->mapWithKeys(function ($option) {
            try {
                $parts = explode('=', $option);
                return [$parts[0] => $parts[1]];
            } catch (\Throwable $e) {
                return [];
            }
        })->sortKeys()->toArray();
    }

    public function toString(array $options): string
    {
        ksort($options);
        return collect($options)->map(function (mixed $value, string $key) {
            return urlencode($key) . '=' . urlencode($value);
        })->join(',');
    }
}
