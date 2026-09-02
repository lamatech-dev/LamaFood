{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach($pages as $page)
@foreach($locales as $locale)
    <url>
        <loc>{{ url($localeRegistry->publicPath($locale, $page->slug === 'home' ? '' : $page->slug)) }}</loc>
@foreach($locales as $alternate)
        <xhtml:link rel="alternate" hreflang="{{ $alternate }}" href="{{ url($localeRegistry->publicPath($alternate, $page->slug === 'home' ? '' : $page->slug)) }}" />
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ url($localeRegistry->publicPath('fa', $page->slug === 'home' ? '' : $page->slug)) }}" />
        <lastmod>{{ ($page->published_at ?? $page->updated_at)->toAtomString() }}</lastmod>
    </url>
@endforeach
@endforeach
@foreach($locales as $locale)
    <url>
        <loc>{{ url($localeRegistry->publicPath($locale, 'menu')) }}</loc>
@foreach($locales as $alternate)
        <xhtml:link rel="alternate" hreflang="{{ $alternate }}" href="{{ url($localeRegistry->publicPath($alternate, 'menu')) }}" />
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ url($localeRegistry->publicPath('fa', 'menu')) }}" />
    </url>
@endforeach
</urlset>
