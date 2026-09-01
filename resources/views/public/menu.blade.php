<!doctype html>
<html lang="{{ $locale }}" dir="{{ $localeMetadata['direction'] }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071015">
    <title>{{ __('public.menu_title', locale: $locale) }}</title>
    <meta name="description" content="{{ __('public.menu_description', locale: $locale) }}">
    <link rel="canonical" href="{{ route('public.menu', ['locale' => $locale, ...$menuQuery]) }}">
    @foreach($locales as $code => $metadata)<link rel="alternate" hreflang="{{ $code }}" href="{{ route('public.menu', ['locale' => $code, ...$menuQuery]) }}">@endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url('/fa/menu') }}">
    @vite('resources/js/app.js')
</head>
<body class="public-site menu-page">
<a class="skip-link" href="#menu-content">{{ __('public.skip', locale: $locale) }}</a>
<header class="site-header"><div class="shell nav-shell">
    <a class="brand" href="{{ url('/'.$locale) }}"><span class="brand-mark">D</span><span>DENARDI<small>ART · COFFEE · JUICE</small></span></a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation">{{ __('public.menu_toggle', locale: $locale) }}</button>
    <nav id="site-navigation" class="site-nav"><a href="{{ url('/'.$locale) }}">{{ __('public.home', locale: $locale) }}</a><a href="{{ url('/'.$locale.'/menu') }}">{{ __('public.menu', locale: $locale) }}</a><a href="{{ url('/'.$locale.'/about') }}">{{ __('public.about', locale: $locale) }}</a><a href="{{ url('/'.$locale.'/contact') }}">{{ __('public.contact', locale: $locale) }}</a></nav>
    <div class="language-switcher">@foreach($locales as $code => $metadata)<a href="{{ route('public.menu', ['locale' => $code, ...$menuQuery]) }}" lang="{{ $code }}" dir="{{ $metadata['direction'] }}" @class(['active' => $code === $locale])>{{ $code === 'fa' ? 'فا' : ($code === 'ar' ? 'عر' : 'EN') }}</a>@endforeach</div>
</div></header>

<main id="menu-content">
    <section class="menu-hero shell"><p class="eyebrow">ART · COFFEE · JUICE</p><h1>{{ __('public.menu_heading', locale: $locale) }}</h1><p>{{ __('public.menu_intro', locale: $locale) }}</p></section>
    <div class="category-bar"><div class="shell category-scroll">@foreach($categories as $category)<a href="#category-{{ $category->public_id }}">{{ $category->translations->first()?->name }}</a>@endforeach</div></div>
    <div class="shell menu-layout">
        <label class="menu-search"><span>{{ __('public.search', locale: $locale) }}</span><input type="search" data-menu-search placeholder="{{ __('public.search_placeholder', locale: $locale) }}"></label>
        @forelse($categories as $category)
            <section id="category-{{ $category->public_id }}" class="menu-category">
                <header><p class="eyebrow">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p><h2>{{ $category->translations->first()?->name }}</h2><p>{{ $category->translations->first()?->description }}</p></header>
                <div class="product-grid">
                    @foreach($category->products as $product)
                        @php($translation = $product->translations->first())
                        @php($setting = $product->branchSettings->first())
                        <article class="product-card" data-product-name="{{ mb_strtolower($translation?->name ?? '') }}">
                            <div class="product-copy"><div class="product-title"><h3>{{ $translation?->name }}</h3><span>{{ number_format(intdiv($setting?->price_amount ?? 0, 10)) }} {{ __('public.toman', locale: $locale) }}</span></div><p>{{ $translation?->description }}</p>@if($translation?->allergen_notice)<small>{{ $translation->allergen_notice }}</small>@endif</div>
                            <div class="product-badges">@if($product->is_new)<b>{{ __('public.new', locale: $locale) }}</b>@endif @if($product->is_best_seller)<b>{{ __('public.best_seller', locale: $locale) }}</b>@endif @if($setting?->availability_state->value === 'sold_out')<b class="sold-out">{{ __('public.sold_out', locale: $locale) }}</b>@endif</div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="empty-state"><h2>{{ __('public.menu_empty', locale: $locale) }}</h2></div>
        @endforelse
        <p class="search-empty" hidden>{{ __('public.search_empty', locale: $locale) }}</p>
    </div>
</main>
</body>
</html>
