@php($isSoldOut = $setting?->availability_state->value === 'sold_out')
<article @class(['product-card', 'is-sold-out' => $isSoldOut, 'is-featured' => $product->is_featured, 'has-image' => $product->primaryMedia]) data-product-name="{{ mb_strtolower(($translation?->name ?? '').' '.($translation?->description ?? '')) }}" data-analytics-type="product_view" data-analytics-subject="{{ $product->public_id }}">
    <figure class="product-media">
        @if($product->is_featured)
            <span class="product-ribbon ribbon-featured">{{ __('public.signature', locale: $locale) }}</span>
        @elseif($product->is_new)
            <span class="product-ribbon ribbon-new">{{ __('public.new', locale: $locale) }}</span>
        @endif
        @if($product->primaryMedia)
            <img class="product-image" src="{{ asset('storage/'.($product->primaryMedia->optimized_path ?: $product->primaryMedia->path)) }}" @if($product->primaryMedia->thumbnail_path && $product->primaryMedia->width > 480) srcset="{{ asset('storage/'.$product->primaryMedia->thumbnail_path) }} 480w, {{ asset('storage/'.($product->primaryMedia->optimized_path ?: $product->primaryMedia->path)) }} {{ min(1600, $product->primaryMedia->width) }}w" sizes="(max-width: 620px) 44vw, (max-width: 920px) 45vw, 440px" @endif data-image-fallback="/denardi-icon.svg" alt="{{ $product->primaryMedia->translations->first()?->alt ?: $translation?->name }}" loading="lazy" decoding="async" width="720" height="720">
        @else
            <span class="product-image-fallback" aria-hidden="true"><span>D</span><small>DENARDI</small></span>
        @endif
        @if($isSoldOut)<span class="product-unavailable" aria-hidden="true">{{ __('public.sold_out', locale: $locale) }}</span>@endif
    </figure>
    <div class="product-copy">
        <div class="product-title"><h3>{{ $translation?->name }}</h3><span class="product-price"><bdi>{{ number_format(intdiv($setting?->price_amount ?? 0, 10)) }}</bdi> <small>{{ __('public.toman', locale: $locale) }}</small></span></div>
        <div class="product-details">
            @if($translation?->description)<p class="product-description">{{ $translation->description }}</p>@endif
            @if($translation?->allergen_notice)<small class="allergen-notice">{{ $translation->allergen_notice }}</small>@endif
            <div class="product-meta">
                <span @class(['availability', 'sold-out' => $isSoldOut])>{{ $isSoldOut ? __('public.sold_out', locale: $locale) : __('public.available', locale: $locale) }}</span>
                @if($product->is_featured)<span class="meta-featured">{{ __('public.signature', locale: $locale) }}</span>@endif
                @if($product->is_best_seller)<span>{{ __('public.popular', locale: $locale) }}</span>@endif
                @if($product->is_new)<span class="meta-new">{{ __('public.new', locale: $locale) }}</span>@endif
            </div>
        </div>
    </div>
</article>
