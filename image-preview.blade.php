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

<--- image showing formating in laravel -------->

@php
    $selling_price = $product->defaultprice->selling_price ?? 0;
    $mrp = $product->defaultprice->mrp ?? 0;
@endphp
<div class="card ProBlock {{ $selling_price == 0 && $mrp == 0 ? 'sold-out' : '' }}">
    <a href="{{ route('product', ['alias' => $product->alias]) }}" class="card-header">
        <x-image.image-preview :image="$product->image" path="pr" :options="['lazy' => true, 'alt' => $product->title, 'width' => '200', 'height' => '200']" />
    </a>
    <a href="{{ route('product', ['alias' => $product->alias]) }}" class="card-body">
        <h3>{{ $product->title }}
            @if (!empty($product->defaultprice))
                ({{ $product->defaultprice->sku }})
            @endif
        </h3>
        <span class="text-secondary Bname">By <span>{{ $product->brand->name ?? $product->category->name }}</span></span>


        @if (($selling_price == 0 && $mrp == 0) || $product->defaultprice->stock_qty == 0)
            <div class="price mt-2 text-danger"><small>Sold Out</small></div>
        @elseif($selling_price >= $mrp)
            <div class="price mt-2">{{ currency()[1] }} {{ $selling_price ?? '' }}/- </div>
        @else
            <div class="price mt-2">{{ currency()[1] }} {{ $selling_price ?? '' }}/- <del><i
                        class="rupee">&#8377;</i>{{ $mrp ?? '' }}/-</del></div>
        @endif
    </a>
    @if ($selling_price != 0 && $mrp != 0 && $product->defaultprice->stock_qty > 0)
        <div class="card-footer">
            <span cart-data-id="{{ $product->id }}" class="btn btn-thm2 addtocart"> Add to cart</span>
            <span buy-data-id="{{ $product->id }}" class="btn btn-thm1 quickbuy"> Quick Buy</span>
        </div>
    @endif
</div>

<-------- products showing component -------->


            
<x-product-box :data="$product" />

<-------- product value and data send --------->


    
