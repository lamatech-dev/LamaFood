<footer class="site-footer">
    <div class="shell footer-grid">
        <div class="footer-brand"><strong>DENARDI</strong><p>ART · COFFEE · JUICE</p></div>
        <nav class="footer-nav" aria-label="{{ __('public.footer_navigation', locale: $locale) }}">
            <a href="{{ url($localeRegistry->publicPath($locale, 'menu')) }}">{{ __('public.menu', locale: $locale) }}</a>
            <a href="{{ url($localeRegistry->publicPath($locale, 'about')) }}">{{ __('public.about', locale: $locale) }}</a>
            <a href="{{ url($localeRegistry->publicPath($locale, 'contact')) }}">{{ __('public.contact', locale: $locale) }}</a>
        </nav>
        <div class="footer-actions">
            @if(config('denardi.phone'))<a href="tel:{{ config('denardi.phone') }}">{{ __('public.call', locale: $locale) }}</a>@endif
            @if(config('denardi.map_url'))<a href="{{ config('denardi.map_url') }}" rel="noopener">{{ __('public.location', locale: $locale) }}</a>@endif
            @if(config('denardi.instagram_url'))<a href="{{ config('denardi.instagram_url') }}" rel="noopener">Instagram</a>@endif
        </div>
        <small>{{ __('public.built_by', locale: $locale) }} <b>Lamatech</b></small>
    </div>
</footer>
