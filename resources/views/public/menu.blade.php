<!doctype html>
<html lang="{{ $locale }}" dir="{{ $localeMetadata['direction'] }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#071015">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/denardi-icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <title>{{ __('public.menu_title', locale: $locale) }}</title>
    <meta name="description" content="{{ __('public.menu_description', locale: $locale) }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ __('public.menu_title', locale: $locale) }}">
    <meta property="og:description" content="{{ __('public.menu_description', locale: $locale) }}">
    <meta property="og:url" content="{{ route('public.menu', ['locale' => $locale, ...$menuQuery]) }}">
    <meta property="og:locale" content="{{ $locale }}">
    <link rel="canonical" href="{{ route('public.menu', ['locale' => $locale, ...$menuQuery]) }}">
    @foreach($locales as $code => $metadata)<link rel="alternate" hreflang="{{ $code }}" href="{{ route('public.menu', ['locale' => $code, ...$menuQuery]) }}">@endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url('/fa/menu') }}">
    <script type="application/ld+json">{!! \Illuminate\Support\Js::encode($structuredData) !!}</script>
    @vite('resources/js/app.js')
</head>
<body class="public-site menu-page">
<a class="skip-link" href="#menu-content">{{ __('public.skip', locale: $locale) }}</a>
<header class="site-header"><div class="shell nav-shell">
    <a class="brand" href="{{ url('/'.$locale) }}" aria-label="Denardi"><span class="brand-mark" aria-hidden="true">D</span><span>DENARDI<small>ART · COFFEE · JUICE</small></span></a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation"><span aria-hidden="true"></span>{{ __('public.menu_toggle', locale: $locale) }}</button>
    <nav id="site-navigation" class="site-nav" aria-label="{{ __('public.navigation', locale: $locale) }}"><a href="{{ url('/'.$locale) }}">{{ __('public.home', locale: $locale) }}</a><a href="{{ url('/'.$locale.'/menu') }}" aria-current="page">{{ __('public.menu', locale: $locale) }}</a><a href="{{ url('/'.$locale.'/about') }}">{{ __('public.about', locale: $locale) }}</a><a href="{{ url('/'.$locale.'/contact') }}">{{ __('public.contact', locale: $locale) }}</a></nav>
    <nav class="language-switcher" aria-label="{{ __('public.languages', locale: $locale) }}">@foreach($locales as $code => $metadata)<a href="{{ route('public.menu', ['locale' => $code, ...$menuQuery]) }}" lang="{{ $code }}" dir="{{ $metadata['direction'] }}" @class(['active' => $code === $locale]) @if($code === $locale) aria-current="page" @endif>{{ $code === 'fa' ? 'فا' : ($code === 'ar' ? 'عر' : 'EN') }}</a>@endforeach</nav>
</div></header>

<main id="menu-content" data-menu-analytics data-analytics-endpoint="{{ route('public.analytics.views') }}" data-analytics-locale="{{ $locale }}" data-analytics-branch="{{ $branchSlug }}">
    <header class="menu-hero shell"><div><p class="eyebrow">ART · COFFEE · JUICE</p><h1>{{ __('public.menu_heading', locale: $locale) }}</h1></div><p>{{ __('public.menu_intro', locale: $locale) }}</p></header>
    <div class="category-bar"><nav class="shell category-scroll" aria-label="{{ __('public.categories', locale: $locale) }}">@foreach($categories as $category)<a href="#category-{{ $category->public_id }}" @if($loop->first) class="active" aria-current="true" @endif>{{ $category->translations->first()?->name }}</a>@endforeach</nav></div>
    <div class="shell menu-tools">
        <label class="menu-search"><span>{{ __('public.search', locale: $locale) }}</span><input type="search" data-menu-search autocomplete="off" placeholder="{{ __('public.search_placeholder', locale: $locale) }}" aria-describedby="menu-search-status"></label>
        <output id="menu-search-status" class="search-status" data-search-status aria-live="polite" data-result-label="{{ __('public.results', locale: $locale) }}">{{ $categories->sum(fn ($category) => $category->products->count()) }} {{ __('public.results', locale: $locale) }}</output>
    </div>
    <div class="shell menu-layout">
        @forelse($categories as $category)
            <section id="category-{{ $category->public_id }}" class="menu-category" data-menu-category data-analytics-type="category_view" data-analytics-subject="{{ $category->public_id }}">
                <header><p class="section-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p><h2>{{ $category->translations->first()?->name }}</h2><p>{{ $category->translations->first()?->description }}</p></header>
                <div class="product-grid">
                    @foreach($category->products as $product)
                        @php($translation = $product->translations->first())
                        @php($setting = $product->branchSettings->first())
                        @include('public.partials.product-card', ['product' => $product, 'translation' => $translation, 'setting' => $setting])
                    @endforeach
                </div>
            </section>
        @empty
            <div class="empty-state"><h2>{{ __('public.menu_empty', locale: $locale) }}</h2></div>
        @endforelse
        <p class="search-empty" hidden>{{ __('public.search_empty', locale: $locale) }}</p>
    </div>
</main>
@include('public.partials.footer')
</body>
</html>
