@php($isSoldOut = $setting?->availability_state->value === 'sold_out')
<article @class(['product-card', 'is-sold-out' => $isSoldOut]) data-product-name="{{ mb_strtolower(($translation?->name ?? '').' '.($translation?->description ?? '')) }}" data-analytics-type="product_view" data-analytics-subject="{{ $product->public_id }}">
    <figure class="product-media">
        @if($product->primaryMedia)
            <img class="product-image" src="{{ asset('storage/'.($product->primaryMedia->optimized_path ?: $product->primaryMedia->path)) }}" data-image-fallback="/denardi-icon.svg" alt="{{ $product->primaryMedia->translations->first()?->alt ?: $translation?->name }}" loading="lazy" width="720" height="480">
        @else
            <span class="product-image-fallback" aria-hidden="true"><img src="/denardi-icon.svg" alt="" width="72" height="72"></span>
        @endif
    </figure>
    <div class="product-copy">
        <div class="product-title"><h3>{{ $translation?->name }}</h3><span class="product-price">{{ number_format(intdiv($setting?->price_amount ?? 0, 10)) }} <small>{{ __('public.toman', locale: $locale) }}</small></span></div>
        @if($translation?->description)<p>{{ $translation->description }}</p>@endif
        @if($translation?->allergen_notice)<small class="allergen-notice">{{ $translation->allergen_notice }}</small>@endif
        <div class="product-meta">
            <span @class(['availability', 'sold-out' => $isSoldOut])>{{ $isSoldOut ? __('public.sold_out', locale: $locale) : __('public.available', locale: $locale) }}</span>
            @if($product->is_featured)<span>{{ __('public.signature', locale: $locale) }}</span>@endif
            @if($product->is_best_seller)<span>{{ __('public.popular', locale: $locale) }}</span>@endif
            @if($product->is_new)<span>{{ __('public.new', locale: $locale) }}</span>@endif
        </div>
    </div>
</article>
