{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}

<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <url>
        <loc>{{ route('shop.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    <url>
        <loc>{{ route('blog.index') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>{{ route('brand.index') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    @foreach($categories as $category)
    <url>
        <loc>{{ route('category.products',$category->slug) }}</loc>
        <lastmod>{{ optional($category->updated_at)->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    @foreach($brands as $brand)
    <url>
        <loc>{{ route('brand.products',$brand->slug) }}</loc>
        <lastmod>{{ optional($brand->updated_at)->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    @foreach($products as $product)
    <url>
        <loc>{{ route('product.details',$product->slug) }}</loc>
        <lastmod>{{ optional($product->updated_at)->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    @endforeach

    @foreach($blogs as $blog)
    <url>
        <loc>{{ route('blog.details',$blog->slug) }}</loc>
        <lastmod>{{ optional($blog->updated_at)->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach

    @foreach($pages as $page)
    <url>
        <loc>{{ route('frontend.page',$page->slug) }}</loc>
        <lastmod>{{ optional($page->updated_at)->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    @endforeach

</urlset>