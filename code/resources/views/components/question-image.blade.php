@props(['mediaLink' => null, 'imageUrl' => null, 'uploadedImage' => null, 'alt' => 'Question Image', 'class' => 'rounded-2xl max-h-full max-w-full object-contain'])

@php
    $hasImage = !empty($uploadedImage) || !empty($mediaLink) || !empty($imageUrl);
    
    // Get the app URL
    $appUrl = config('app.url');
    
    // Check if mediaLink or imageUrl is an external URL (not from our own domain)
    $sourceUrl = $mediaLink ?: $imageUrl;
    $isExternalUrl = (!empty($sourceUrl) && 
                     (str_starts_with($sourceUrl, 'https://') || str_starts_with($sourceUrl, 'http://')) &&
                     !str_starts_with($sourceUrl, $appUrl));
                         
    // Convert video URLs to embed format
    $embedUrl = $sourceUrl;
    if ($isExternalUrl) {
        // Handle various YouTube URL formats
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $sourceUrl, $matches)) {
            $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
        }
        // Handle YouTube Shorts
        elseif (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/', $sourceUrl, $matches)) {
            $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
        }
        // Handle Vimeo URLs
        elseif (preg_match('/vimeo\.com\/(?:video\/)?([0-9]+)/', $sourceUrl, $matches)) {
            $embedUrl = 'https://player.vimeo.com/video/' . $matches[1];
        }
    } else {
        // For local images, build the image source
        $embedUrl = $imageUrl ?: ($mediaLink ? route('question.image', ['filename' => $mediaLink]) : null);
    }
@endphp

@if ($hasImage && ($embedUrl || $isExternalUrl))
    @if ($isExternalUrl)
        <iframe src="{{ $embedUrl }}"
            title="{{ $alt }}"
            {{ $attributes->merge(['class' => 'w-full h-full rounded-2xl']) }}
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
        </iframe>
    @else
        <img src="{{ $embedUrl }}"
            alt="{{ $alt }}" 
            {{ $attributes->merge(['class' => $class]) }}>
    @endif
@else
    <span class="text-zinc-500 dark:text-zinc-400">{{ __('testcreation.no_image_uploaded') }}</span>
@endif
