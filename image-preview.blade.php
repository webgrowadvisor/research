<picture>
    @if (!empty($image))
        @php
            $exten = explode('.', $image);
            $lastValue = array_pop($exten);
        @endphp

        @if (strtoupper($lastValue) != 'WEBP' && file_exists(public_path('up/' . $path . '/webp/' . implode($exten) . '.webp')))
            <source srcset="{{ asset('up/' . $path . '/webp/' . implode($exten) . '.webp') }}">
        @endif

        @if (file_exists(public_path('up/' . $path . '/' . $image)))
            <img src="{{ asset('up/' . $path . '/' . $image) }}"
                @if (!empty($options['class'])) class="{{ $options['class'] }}" @endif
                @if (!empty($options['width'])) width="{{ $options['width'] }}" @endif
                @if (!empty($options['height'])) height="{{ $options['height'] }}" @endif
                @if (!empty($options['alt'])) alt="{{ $options['alt'] }}" @endif
                @if (!empty($options['lazy'])) loading="lazy" @endif
                @if (!empty($options['id'])) id="{{ $options['id'] }}" @endif>
        @elseif(file_exists(public_path('up/' . $path . '/og/' . $image)))
            <img src="{{ asset('up/' . $path . '/og/' . $image) }}"
                @if (!empty($options['class'])) class="{{ $options['class'] }}" @endif
                @if (!empty($options['width'])) width="{{ $options['width'] }}" @endif
                @if (!empty($options['height'])) height="{{ $options['height'] }}" @endif
                @if (!empty($options['alt'])) alt="{{ $options['alt'] }}" @endif
                @if (!empty($options['lazy'])) loading="lazy" @endif
                @if (!empty($options['id'])) id="{{ $options['id'] }}" @endif>
        @else
            <img src='{{ asset('frontend/img/no-img.jpg') }}'
                @if (!empty($options['class'])) class="{{ $options['class'] }}" @endif
                @if (!empty($options['width'])) width="{{ $options['width'] }}" @endif
                @if (!empty($options['height'])) height="{{ $options['height'] }}" @endif
                @if (!empty($options['alt'])) alt="{{ $options['alt'] }}" @endif
                @if (!empty($options['lazy'])) loading="lazy" @endif
                @if (!empty($options['id'])) id="{{ $options['id'] }}" @endif>
        @endif
    @else
        <img src='{{ asset('frontend/img/no-img.jpg') }}'
            @if (!empty($options['class'])) class="{{ $options['class'] }}" @endif
            @if (!empty($options['width'])) width="{{ $options['width'] }}" @endif
            @if (!empty($options['height'])) height="{{ $options['height'] }}" @endif
            @if (!empty($options['alt'])) alt="{{ $options['alt'] }}" @endif
            @if (!empty($options['lazy'])) loading="lazy" @endif
            @if (!empty($options['id'])) id="{{ $options['id'] }}" @endif>
    @endif
</picture>
