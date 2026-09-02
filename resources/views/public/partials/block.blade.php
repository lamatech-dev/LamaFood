@php($type = $block['type'])
@php($heroMedia = data_get($block, 'media.'.data_get($block, 'structure.mediaId')))
@if($type === 'hero')
    <section class="hero-block" @if($heroMedia) style="--hero-image: url('{{ asset('storage/'.($heroMedia['optimized_path'] ?: $heroMedia['path'])) }}')" @endif>
        <div class="shell hero-content">
            @if($content['eyebrow'] ?? null)<p class="eyebrow">{{ $content['eyebrow'] }}</p>@endif
            <h1>{{ $content['title'] }}</h1>
            @if($content['body'] ?? null)<p class="hero-copy">{{ $content['body'] }}</p>@endif
            <a class="button primary" href="{{ data_get($block, 'structure.ctaTarget', url('/'.$locale.'/menu')) }}">{{ $content['ctaLabel'] ?? __('public.menu', locale: $locale) }}</a>
        </div>
    </section>
@elseif($type === 'about')
    <section class="content-section"><div class="shell split"><p class="section-number">01</p><div><h2>{{ $content['heading'] }}</h2><p class="large-copy">{{ $content['body'] }}</p></div></div></section>
@elseif($type === 'gallery')
    @php($galleryMedia = collect(data_get($block, 'structure.mediaIds', []))->map(fn ($id) => data_get($block, 'media.'.$id))->filter()->values())
    <section class="content-section gallery-section"><div class="shell"><h2>{{ $content['heading'] ?? '' }}</h2>@if($galleryMedia->isNotEmpty())<div class="gallery-grid">@foreach($galleryMedia as $media)<img src="{{ asset('storage/'.($media['optimized_path'] ?: $media['path'])) }}" alt="{{ data_get($media, 'translations.'.$locale.'.alt', '') }}" loading="lazy">@endforeach</div>@endif @if($content['caption'] ?? null)<p>{{ $content['caption'] }}</p>@endif</div></section>
@elseif($type === 'menu_preview')
    <section class="content-section menu-preview"><div class="shell split"><div><p class="eyebrow">DENARDI MENU</p><h2>{{ $content['heading'] }}</h2><p>{{ $content['intro'] ?? '' }}</p><a class="button" href="{{ url('/'.$locale.'/menu') }}">{{ $content['ctaLabel'] ?? __('public.menu', locale: $locale) }}</a></div><div class="menu-brand-panel" aria-hidden="true"><img src="/denardi-icon.svg" alt="" width="144" height="144"><p>ART · COFFEE · JUICE</p></div></div></section>
@elseif(in_array($type, ['location', 'contact'], true))
    <section class="content-section"><div class="shell split"><p class="section-number">{{ $type === 'location' ? '02' : '03' }}</p><div><h2>{{ $content['heading'] }}</h2><p class="large-copy">{{ $content['address'] ?? $content['body'] ?? '' }}</p>@if(data_get($block, 'structure.mapUrl'))<a class="text-link" href="{{ data_get($block, 'structure.mapUrl') }}" rel="noopener">{{ $content['directionsLabel'] ?? '' }}</a>@endif</div></div></section>
@elseif($type === 'cta')
    <section class="content-section"><div class="shell cta-panel"><div><h2>{{ $content['heading'] }}</h2><p>{{ $content['body'] ?? '' }}</p></div><a class="button primary" href="{{ data_get($block, 'structure.target') }}">{{ $content['label'] }}</a></div></section>
@endif
