<!doctype html>
<html lang="{{ $locale }}" dir="{{ $localeMetadata['direction'] }}">
<head>
    <meta charset="utf-8">
    <link rel="preload" href="/fonts/vazirmatn/Vazirmatn.woff2" as="font" type="font/woff2" crossorigin>
    @if(app()->isLocal())<meta name="robots" content="noindex,nofollow">@endif
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071015">
    @if($isPreview ?? false)<meta name="robots" content="noindex,nofollow">@endif
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/denardi-icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <title>{{ $translation['meta_title'] ?: $translation['title'].' · Denardi' }}</title>
    @if($translation['meta_description'])<meta name="description" content="{{ $translation['meta_description'] }}">@endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $translation['og_title'] ?: ($translation['meta_title'] ?: $translation['title'].' · Denardi') }}">
    @if($translation['og_description'] ?: $translation['meta_description'])<meta property="og:description" content="{{ $translation['og_description'] ?: $translation['meta_description'] }}">@endif
    <meta property="og:url" content="{{ url($localeRegistry->publicPath($locale, $slug === 'home' ? '' : $slug)) }}">
    <meta property="og:locale" content="{{ $locale }}">
    <link rel="canonical" href="{{ url($localeRegistry->publicPath($locale, $slug === 'home' ? '' : $slug)) }}">
    @foreach($locales as $code => $metadata)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ url($localeRegistry->publicPath($code, $slug === 'home' ? '' : $slug)) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url($localeRegistry->publicPath('fa', $slug === 'home' ? '' : $slug)) }}">
    @isset($structuredData)<script type="application/ld+json">{!! \Illuminate\Support\Js::encode($structuredData) !!}</script>@endisset
    @vite('resources/js/app.js')
</head>
<body class="public-site" data-page="{{ $slug }}">
@if($isPreview ?? false)<div class="preview-banner">PREVIEW · {{ strtoupper($locale) }} · {{ $translation['title'] }}</div>@endif
<a class="skip-link" href="#content">{{ __('public.skip', locale: $locale) }}</a>
<header class="site-header">
    <div class="shell nav-shell">
        <a class="brand" href="{{ url($localeRegistry->publicPath($locale)) }}">
            <span class="brand-mark" aria-hidden="true">D</span>
            <span>DENARDI<small>ART · COFFEE · JUICE</small></span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation"><span aria-hidden="true"></span>{{ __('public.menu_toggle', locale: $locale) }}</button>
        <nav id="site-navigation" class="site-nav" aria-label="{{ __('public.navigation', locale: $locale) }}">
            <a href="{{ url($localeRegistry->publicPath($locale)) }}" @if($slug === 'home') aria-current="page" @endif>{{ __('public.home', locale: $locale) }}</a>
            <a href="{{ url($localeRegistry->publicPath($locale, 'menu')) }}">{{ __('public.menu', locale: $locale) }}</a>
            <a href="{{ url($localeRegistry->publicPath($locale, 'about')) }}" @if($slug === 'about') aria-current="page" @endif>{{ __('public.about', locale: $locale) }}</a>
            <a href="{{ url($localeRegistry->publicPath($locale, 'contact')) }}" @if($slug === 'contact') aria-current="page" @endif>{{ __('public.contact', locale: $locale) }}</a>
        </nav>
        <nav class="language-switcher" aria-label="{{ __('public.languages', locale: $locale) }}">
            @foreach($locales as $code => $metadata)
                <a href="{{ url($localeRegistry->publicPath($code, $slug === 'home' ? '' : $slug)) }}" lang="{{ $code }}" dir="{{ $metadata['direction'] }}" @class(['active' => $code === $locale]) @if($code === $locale) aria-current="page" @endif>{{ $code === 'fa' ? 'فا' : ($code === 'ar' ? 'عر' : 'EN') }}</a>
            @endforeach
        </nav>
    </div>
</header>

<main id="content">
    @if($slug !== 'home')
        <header class="page-intro shell">
            <p class="eyebrow">DENARDI / {{ strtoupper($slug) }} · {{ strtoupper($locale) }}</p>
            <h1>{{ $translation['title'] }}</h1>
            <span class="page-intro-line" aria-hidden="true"></span>
        </header>
    @endif
    @forelse($blocks as $block)
        @include('public.partials.block', ['block' => $block, 'content' => data_get($block, "translations.{$locale}.content_json", [])])
    @empty
        <section class="empty-state shell"><h1>{{ $translation['title'] }}</h1></section>
    @endforelse
    @if($slug === 'home')
        <section class="home-discovery content-section" aria-labelledby="discover-heading">
            <div class="shell">
                <div class="section-heading"><p class="eyebrow">{{ __('public.discover_eyebrow', locale: $locale) }}</p><h2 id="discover-heading">{{ __('public.discover_heading', locale: $locale) }}</h2></div>
                <nav class="discovery-grid" aria-label="{{ __('public.discover_heading', locale: $locale) }}">
                    <a href="{{ url($localeRegistry->publicPath($locale, 'menu')) }}"><span>01</span><h3>{{ __('public.menu', locale: $locale) }}</h3><p>{{ __('public.discover_menu', locale: $locale) }}</p></a>
                    <a href="{{ url($localeRegistry->publicPath($locale, 'about')) }}"><span>02</span><h3>{{ __('public.about', locale: $locale) }}</h3><p>{{ __('public.discover_about', locale: $locale) }}</p></a>
                    <a href="{{ url($localeRegistry->publicPath($locale, 'contact')) }}"><span>03</span><h3>{{ __('public.contact', locale: $locale) }}</h3><p>{{ __('public.discover_contact', locale: $locale) }}</p></a>
                </nav>
            </div>
        </section>
    @endif
</main>
@include('public.partials.footer')
</body>
</html>
