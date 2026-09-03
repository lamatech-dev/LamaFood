@php($type = $block['type'])
@php($heroMedia = data_get($block, 'media.'.data_get($block, 'structure.mediaId')))
@if($type === 'hero')
    <section class="hero-block" @if($heroMedia) style="--hero-image: url('{{ asset('storage/'.($heroMedia['optimized_path'] ?: $heroMedia['path'])) }}')" @endif>
        <div class="shell hero-content">
            @if($content['eyebrow'] ?? null)<p class="eyebrow">{{ $content['eyebrow'] }}</p>@endif
            <h1>{{ $content['title'] }}</h1>
            @if($content['body'] ?? null)<p class="hero-copy">{{ $content['body'] }}</p>@endif
            <a class="button primary" href="{{ data_get($block, 'structure.ctaTarget', url($localeRegistry->publicPath($locale, 'menu'))) }}">{{ $content['ctaLabel'] ?? __('public.menu', locale: $locale) }}</a>
        </div>
        <div class="hero-signature" aria-hidden="true"><span>01</span><b>DENARDI</b><small>ART · COFFEE · JUICE</small></div>
    </section>
@elseif($type === 'about')
    <section class="content-section"><div class="shell split"><p class="section-number">01</p><div><h2>{{ $content['heading'] }}</h2><p class="large-copy">{{ $content['body'] }}</p></div></div></section>
@elseif($type === 'gallery')
    @php($galleryMedia = collect(data_get($block, 'structure.mediaIds', []))->map(fn ($id) => data_get($block, 'media.'.$id))->filter()->values())
    <section class="content-section gallery-section"><div class="shell"><h2>{{ $content['heading'] ?? '' }}</h2>@if($galleryMedia->isNotEmpty())<div class="gallery-grid">@foreach($galleryMedia as $media)<img src="{{ asset('storage/'.($media['optimized_path'] ?: $media['path'])) }}" alt="{{ data_get($media, 'translations.'.$locale.'.alt', '') }}" loading="lazy">@endforeach</div>@endif @if($content['caption'] ?? null)<p>{{ $content['caption'] }}</p>@endif</div></section>
@elseif($type === 'menu_preview')
    @if(isset($featuredProducts) && $featuredProducts->isNotEmpty())
    <section class="content-section featured-preview"><div class="shell">
        <div class="featured-heading"><div><p class="eyebrow">DENARDI / SELECTED</p><h2>{{ $content['heading'] }}</h2><p>{{ $content['intro'] ?? '' }}</p></div><a class="button" href="{{ url($localeRegistry->publicPath($locale, 'menu')) }}">{{ $content['ctaLabel'] }}</a></div>
        <div class="featured-grid">@foreach($featuredProducts as $product)@include('public.partials.product-card', ['product' => $product, 'translation' => $product->translations->first(), 'setting' => $product->branchSettings->first()])@endforeach</div>
    </div></section>
    @else
    <section class="content-section menu-preview"><div class="shell split"><div><p class="eyebrow">DENARDI MENU</p><h2>{{ $content['heading'] }}</h2><p>{{ $content['intro'] ?? '' }}</p><a class="button" href="{{ url($localeRegistry->publicPath($locale, 'menu')) }}">{{ $content['ctaLabel'] ?? __('public.menu', locale: $locale) }}</a></div><div class="menu-brand-panel" aria-hidden="true"><img src="/denardi-icon.svg" alt="" width="144" height="144"><p>ART · COFFEE · JUICE</p></div></div></section>
    @endif
@elseif($type === 'contact')
    <section class="content-section"><div class="shell contact-layout">
        <div class="contact-copy">
            <p class="eyebrow">DENARDI / CONTACT</p><h2>{{ $content['heading'] }}</h2><p>{{ $content['body'] ?? '' }}</p>
            <dl class="contact-details">
                @if(data_get($block, 'structure.phone'))<div><dt>{{ $content['phoneLabel'] ?? __('public.call', locale: $locale) }}</dt><dd><bdi>{{ data_get($block, 'structure.phone') }}</bdi></dd></div>@endif
                @if(data_get($block, 'structure.instagramUrl'))<div><dt>{{ $content['instagramLabel'] ?? 'Instagram' }}</dt><dd>@if(data_get($block, 'structure.variant') === 'demo')<span>Instagram · DEMO</span>@else<a class="text-link" href="{{ data_get($block, 'structure.instagramUrl') }}" rel="noopener">Instagram</a>@endif</dd></div>@endif
            </dl>
            <a class="button primary" href="{{ url($localeRegistry->publicPath($locale, 'menu')) }}">{{ __('public.menu', locale: $locale) }}</a>
        </div>
        @if(data_get($block, 'structure.variant') === 'demo')
        <aside class="contact-map"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/></svg><h3>{{ __('public.demo_map_title', locale: $locale) }}</h3><p>{{ __('public.demo_map_note', locale: $locale) }}</p><span class="eyebrow">DEMO / NOT A REAL LOCATION</span></aside>
        @endif
    </div></section>
@elseif($type === 'location')
    <section class="content-section"><div class="shell split"><p class="section-number">{{ $type === 'location' ? '02' : '03' }}</p><div><h2>{{ $content['heading'] }}</h2><p class="large-copy">{{ $content['address'] ?? $content['body'] ?? '' }}</p>@if(data_get($block, 'structure.mapUrl'))<a class="text-link" href="{{ data_get($block, 'structure.mapUrl') }}" rel="noopener">{{ $content['directionsLabel'] ?? '' }}</a>@endif</div></div></section>
@elseif($type === 'cta')
    <section class="content-section"><div class="shell cta-panel"><div><h2>{{ $content['heading'] }}</h2><p>{{ $content['body'] ?? '' }}</p></div><a class="button primary" href="{{ data_get($block, 'structure.target') }}">{{ $content['label'] }}</a></div></section>
@endif
