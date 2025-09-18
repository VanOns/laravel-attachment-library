@props([
    'fit' => 'contain',
    'imageClass' => '',
    'alt' => '',
    'lightbox' => false,
    'lightboxGallery' => 'gallery-a'
])

@php
    use VanOns\LaravelAttachmentLibrary\Facades\Resizer;
    use VanOns\LaravelAttachmentLibrary\Models\Attachment;

    /**
    * @var string|int|Attachment|null $src
    * @var string $fit
    * @var string $size
    * @var array $breakpoints
    * @var string|int $aspectRatio
    * @var array $formats
    * @var string $class
    * @var string $imageClass
    * @var string $alt
    * @var Attachment $attachment
    */

    $alt = $alt ?: $attachment?->alt ?: '';
    $keys = collect($breakpoints)->keys();
@endphp

<picture @class(['block overflow-hidden', $class]) {{ $attributes->except('class') }}>
    @if($src)
        @if($supportedByGlide)
            @foreach($formats as $format)
                @foreach($keys->reverse() as $breakpoint)
                    @php
                        $index = $keys->search($breakpoint);
                        $nextBreakpoint = $keys->get($index + 1) ?? $keys->get($index);
                        $media = "(min-width: {$breakpoints[$breakpoint]}px)";
                        $width = $breakpoints[$nextBreakpoint];
                        $data = Resizer::src($src)->width($width)->size($size)->aspectRatio($aspectRatio)->format($format)->resize();
                    @endphp

                    <source
                        srcset="{{ $data['url'] }}"
                        media="{{ $media }}"
                        width="{{ $data['width'] }}"
                        height="{{ $data['height'] }}"
                        type="image/{{ $format }}"
                    >
                @endforeach

                @php($data = Resizer::src($src)->width($breakpoints[$keys->first()])->size($size)->aspectRatio($aspectRatio)->format($format)->resize())
                <source
                    srcset="{{ $data['url'] }}"
                    width="{{ $data['width'] }}"
                    height="{{ $data['height'] }}"
                    type="image/{{ $format }}"
                >
            @endforeach
        @endif

        @php($data = Resizer::src($src)->width(end($breakpoints))->size($size)->aspectRatio($aspectRatio)->resize())
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
