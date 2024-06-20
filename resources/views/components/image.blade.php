@props([
    'fit' => 'contain',
    'imageClass' => '',
    'alt' => '',
    'lightbox' => false,
    'lightboxGallery' => 'gallery-a'
])

@php
    /**
    * @var string $path
    * @var string $fit
    * @var array $sizes
    * @var array $breakpoints
    * @var string|int $aspectRatio
    * @var array $formats
    * @var string $class
    * @var string $imageClass
    * @var string $lat
    */

    $keys = collect($breakpoints)->keys();
@endphp

<picture @class(['block overflow-hidden', $class]) {{ $attributes->except('class') }}>
    @if($src)
        @foreach($formats as $format)
            @foreach($keys->reverse() as $breakpoint)
                @php
                    $index = $keys->search($breakpoint);
                    $nextBreakpoint = $keys->get($index + 1) ?? $keys->get($index);
                    $media = "(min-width: {$breakpoints[$breakpoint]}px)";
                    $width = $breakpoints[$nextBreakpoint];
                    $data = \VanOns\LaravelAttachmentLibrary\Facades\Resizer::src($src)->width($width)->size($sizes[$breakpoint])->aspectRatio($aspectRatio)->format($format)->resize();
                @endphp

                <source
                    srcset="{{ $data['url'] }}"
                    media="{{ $media }}"
                    width="{{ $data['width'] }}"
                    height="{{ $data['height'] }}"
                    type="image/{{ $format }}"
                >
            @endforeach

            @php($data = \VanOns\LaravelAttachmentLibrary\Facades\Resizer::src($src)->width($breakpoints[$keys->first()])->size($sizes[$keys->first()])->aspectRatio($aspectRatio)->format($format)->resize())
            <source
                srcset="{{ $data['url'] }}"
                width="{{ $data['width'] }}"
                height="{{ $data['height'] }}"
                type="image/{{ $format }}"
            >
        @endforeach

        @php($data = \VanOns\LaravelAttachmentLibrary\Facades\Resizer::src($src)->width(end($breakpoints))->size($sizes['default'])->aspectRatio($aspectRatio)->resize())
        <img
            src="{{ $data['url'] }}"
            width="{{ $data['width'] }}"
            height="{{ $data['height'] }}"
            alt="{{ $alt }}"

            @if($lightbox)
                data-fancybox="{{ $lightboxGallery }}"
            @endif

            @class([
                'h-full w-full',
                'object-cover' => $fit === 'cover',
                'object-contain' => $fit === 'contain',
                $imageClass,
            ])
        >
    @endif
</picture>
