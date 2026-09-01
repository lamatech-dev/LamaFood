<!doctype html>
<html lang="{{ $locale }}" dir="{{ $localeMetadata['direction'] }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071015">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/denardi-icon.svg" type="image/svg+xml">
    <title>{{ $translation['meta_title'] ?: $translation['title'].' · Denardi' }}</title>
    @if($translation['meta_description'])<meta name="description" content="{{ $translation['meta_description'] }}">@endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $translation['og_title'] ?: ($translation['meta_title'] ?: $translation['title'].' · Denardi') }}">
    @if($translation['og_description'] ?: $translation['meta_description'])<meta property="og:description" content="{{ $translation['og_description'] ?: $translation['meta_description'] }}">@endif
    <meta property="og:url" content="{{ url('/'.$locale.($slug === 'home' ? '' : '/'.$slug)) }}">
    <meta property="og:locale" content="{{ $locale }}">
    <link rel="canonical" href="{{ url('/'.$locale.($slug === 'home' ? '' : '/'.$slug)) }}">
    @foreach($locales as $code => $metadata)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ url('/'.$code.($slug === 'home' ? '' : '/'.$slug)) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url('/fa'.($slug === 'home' ? '' : '/'.$slug)) }}">
    @vite('resources/js/app.js')
</head>
<body class="public-site">
<a class="skip-link" href="#content">{{ __('public.skip', locale: $locale) }}</a>
<header class="site-header">
    <div class="shell nav-shell">
        <a class="brand" href="{{ url('/'.$locale) }}" aria-label="Denardi">
            <span class="brand-mark" aria-hidden="true">D</span>
            <span>DENARDI<small>ART · COFFEE · JUICE</small></span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation">{{ __('public.menu_toggle', locale: $locale) }}</button>
        <nav id="site-navigation" class="site-nav" aria-label="{{ __('public.navigation', locale: $locale) }}">
            <a href="{{ url('/'.$locale) }}">{{ __('public.home', locale: $locale) }}</a>
            <a href="{{ url('/'.$locale.'/menu') }}">{{ __('public.menu', locale: $locale) }}</a>
            <a href="{{ url('/'.$locale.'/about') }}">{{ __('public.about', locale: $locale) }}</a>
            <a href="{{ url('/'.$locale.'/contact') }}">{{ __('public.contact', locale: $locale) }}</a>
        </nav>
        <div class="language-switcher" aria-label="{{ __('public.languages', locale: $locale) }}">
            @foreach($locales as $code => $metadata)
                <a href="{{ url('/'.$code.($slug === 'home' ? '' : '/'.$slug)) }}" lang="{{ $code }}" dir="{{ $metadata['direction'] }}" @class(['active' => $code === $locale])>{{ $code === 'fa' ? 'فا' : ($code === 'ar' ? 'عر' : 'EN') }}</a>
            @endforeach
        </div>
    </div>
</header>

<main id="content">
    @forelse($blocks as $block)
        @include('public.partials.block', ['block' => $block, 'content' => data_get($block, "translations.{$locale}.content_json", [])])
    @empty
        <section class="empty-state shell"><h1>{{ $translation['title'] }}</h1></section>
    @endforelse
</main>

<footer class="site-footer">
    <div class="shell footer-grid">
        <div><strong>DENARDI</strong><p>ART · COFFEE · JUICE</p></div>
        <div class="footer-actions">
            @if(config('denardi.map_url'))<a href="{{ config('denardi.map_url') }}" rel="noopener">{{ __('public.location', locale: $locale) }}</a>@endif
            @if(config('denardi.instagram_url'))<a href="{{ config('denardi.instagram_url') }}" rel="noopener">Instagram</a>@endif
        </div>
        <small>{{ __('public.built_by', locale: $locale) }} <b>Lamatech</b></small>
    </div>
</footer>
</body>
</html>
