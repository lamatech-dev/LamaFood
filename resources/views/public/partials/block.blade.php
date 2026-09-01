@php($type = $block['type'])
@if($type === 'hero')
    <section class="hero-block">
        <div class="hero-glow" aria-hidden="true"></div>
        <div class="shell hero-content">
            @if($content['eyebrow'] ?? null)<p class="eyebrow">{{ $content['eyebrow'] }}</p>@endif
            <h1>{{ $content['title'] }}</h1>
            @if($content['body'] ?? null)<p class="hero-copy">{{ $content['body'] }}</p>@endif
            @if($content['ctaLabel'] ?? null)<a class="button primary" href="{{ data_get($block, 'structure.ctaTarget', '#') }}">{{ $content['ctaLabel'] }}</a>@endif
        </div>
    </section>
@elseif($type === 'about')
    <section class="content-section"><div class="shell split"><p class="section-number">01</p><div><h2>{{ $content['heading'] }}</h2><p class="large-copy">{{ $content['body'] }}</p></div></div></section>
@elseif($type === 'gallery')
    <section class="content-section gallery-section"><div class="shell"><h2>{{ $content['heading'] ?? '' }}</h2><div class="gallery-grid"><div></div><div></div><div></div></div>@if($content['caption'] ?? null)<p>{{ $content['caption'] }}</p>@endif</div></section>
@elseif($type === 'menu_preview')
    <section class="content-section menu-preview"><div class="shell split"><div><p class="eyebrow">DENARDI MENU</p><h2>{{ $content['heading'] }}</h2><p>{{ $content['intro'] ?? '' }}</p><a class="button" href="{{ url('/'.app()->getLocale().'/menu') }}">{{ $content['ctaLabel'] }}</a></div><div class="menu-art" aria-hidden="true"><span>COFFEE</span><span>JUICE</span><span>ART</span></div></div></section>
@elseif(in_array($type, ['location', 'contact'], true))
    <section class="content-section"><div class="shell split"><p class="section-number">{{ $type === 'location' ? '02' : '03' }}</p><div><h2>{{ $content['heading'] }}</h2><p class="large-copy">{{ $content['address'] ?? $content['body'] ?? '' }}</p>@if(data_get($block, 'structure.mapUrl'))<a class="text-link" href="{{ data_get($block, 'structure.mapUrl') }}" rel="noopener">{{ $content['directionsLabel'] ?? '' }}</a>@endif</div></div></section>
@elseif($type === 'cta')
    <section class="content-section"><div class="shell cta-panel"><div><h2>{{ $content['heading'] }}</h2><p>{{ $content['body'] ?? '' }}</p></div><a class="button primary" href="{{ data_get($block, 'structure.target') }}">{{ $content['label'] }}</a></div></section>
@endif
