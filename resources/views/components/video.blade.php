@php
    /**
     * @var \VanOns\LaravelAttachmentLibrary\Models\Attachment|null $attachment
     * @var string|null $posterUrl
     */
@endphp

@if($attachment)
    <video
        @if($attachment->width && $attachment->height)
            width="{{ $attachment->width }}"
            height="{{ $attachment->height }}"
            style="aspect-ratio: {{ $attachment->width }} / {{ $attachment->height }}"
        @endif
        @if($posterUrl)
            poster="{{ $posterUrl }}"
        @endif
        {{ $attributes->merge(['controls' => true, 'preload' => 'metadata']) }}
    >
        <source src="{{ $attachment->url }}" type="{{ $attachment->mime_type }}">

        @foreach($attachment->captions as $caption)
            <track
                kind="captions"
                src="{{ $caption->url }}"
                srclang="{{ $caption->pivot->language }}"
                @if($caption->pivot->label)
                    label="{{ $caption->pivot->label }}"
                @endif
                @if($caption->pivot->is_default)
                    default
                @endif
            >
        @endforeach
    </video>
@endif
