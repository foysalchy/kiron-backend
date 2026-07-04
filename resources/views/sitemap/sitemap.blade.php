{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}

<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">

@foreach($urls as $url)

<url>
    <loc>{{ $url['loc'] }}</loc>
    <lastmod>{{ \Carbon\Carbon::parse($url['lastmod'])->toAtomString() }}</lastmod>
    <changefreq>{{ $url['changefreq'] }}</changefreq>
    <priority>{{ $url['priority'] }}</priority>
</url>

@endforeach

</urlset>