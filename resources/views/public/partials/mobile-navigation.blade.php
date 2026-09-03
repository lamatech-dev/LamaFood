<nav class="menu-dock" aria-label="{{ __('public.quick_navigation', locale: $locale) }}">
    @foreach(['home' => 'home', 'about' => 'info', 'menu' => 'book', 'contact' => 'pin'] as $destination => $icon)
        @php($destinationPath = $localeRegistry->publicPath($locale, $destination === 'home' ? '' : $destination))
        @php($destinationQuery = $destination === 'menu' && !empty($menuQuery) ? '?'.http_build_query($menuQuery) : '')
        <a href="{{ url($destinationPath).$destinationQuery }}" data-dock-page="{{ $destination }}" @if($currentPage === $destination) aria-current="page" @endif>
            <span class="dock-icon-wrap" aria-hidden="true"><span class="menu-icon icon-{{ $icon }}"></span></span>
            <span class="dock-label">{{ __('public.'.$destination, locale: $locale) }}</span>
        </a>
    @endforeach
</nav>
