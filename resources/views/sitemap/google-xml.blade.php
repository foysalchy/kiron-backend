{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
    <channel>
        <title>{{ $setup->shop_name ?? ($setup->title ?? 'Our Store') }} Products</title>
        <link>{{ url('/') }}</link>
        <description>Product feed for Google Merchant Center - {{ $setup->shop_name ?? ($setup->title ?? 'Our Store') }}
        </description>
        @foreach($products as $product)
            @php
                // মেইন প্রোডাক্টের SKU বের করা (অ্যারে থেকে প্রথমটি)
                $mainSku = is_array($product->sku_code) ? collect($product->sku_code)->first() : $product->sku_code;
                $mainSku = $mainSku ?: 'PRD-' . $product->id;
            @endphp

            @if($product->type === 'single')
                {{-- ─── Single Product Item ─── --}}
                <item>
                    <g:id>{{ $mainSku }}</g:id>
                    <g:title>{{ $product->title }}</g:title>
                    <g:description>{{ strip_tags($product->short_description ?: $product->title) }}</g:description>
                    <g:link>{{ route('product.details', $product->slug) }}</g:link>
                    <g:image_link>{{ $product->thumbnail_url }}</g:image_link>

                    {{-- অতিরিক্ত ইমেজ লিঙ্ক (Additional Images) --}}
                    @foreach($product->galleries as $gallery)
                        <g:additional_image_link>{{ $gallery->image_url }}</g:additional_image_link>
                    @endforeach

                    <g:condition>new</g:condition>
                    <g:availability>{{ $product->available_stock > 0 ? 'in_stock' : 'out_of_stock' }}</g:availability>
                    <g:price>{{ number_format($product->regular_price, 2, '.', '') }} {{ $setup->currency ?: 'BDT' }}</g:price>
                    @if($product->discount > 0)
                        <g:sale_price>{{ number_format($product->sale_price, 2, '.', '') }} {{ $setup->currency ?: 'BDT' }}
                        </g:sale_price>
                    @endif
                    <g:brand>{{ $product->brand->name ?? ($setup->shop_name ?: 'Generic') }}</g:brand>
                    <g:identifier_exists>no</g:identifier_exists>
                    <g:google_product_category>{{ $product->mega_categories->first()->name ?? 'General' }}
                    </g:google_product_category>
                </item>
            @else
                {{-- ─── Variable Product Items ─── --}}
                @foreach($product->variations as $variant)
                    @php
                        // ভ্যারিয়েন্টের নিজস্ব SKU থাকলে সেটি, নাহলে VAR-ID
                        $variantId = $variant->sku ?: 'VAR-' . $variant->id;
                    @endphp
                    <item>
                        {{-- আপনার রিকোয়ারমেন্ট অনুযায়ী এখানে ভ্যারিয়েন্ট SKU হবে --}}
                        <g:id>{{ $variantId }}</g:id>
                        <g:item_group_id>GRP-{{ $product->id }}</g:item_group_id>
                        <g:title>{{ $product->title }} - {{ $variant->display_name }}</g:title>
                        <g:description>{{ strip_tags($product->short_description ?: $product->title) }}</g:description>
                        <g:link>{{ route('product.details', $product->slug) }}</g:link>

                        {{-- ভ্যারিয়েন্ট ইমেজ --}}
                        <g:image_link>{{ $variant->image ? asset('storage/' . $variant->image) : $product->thumbnail_url }}
                        </g:image_link>

                        {{-- ভ্যারিয়েন্ট এর অতিরিক্ত ইমেজ থাকলে, নাহলে মেইন প্রোডাক্টের গুলো --}}
                        @if($variant->galleries->count() > 0)
                            @foreach($variant->galleries as $vGallery)
                                <g:additional_image_link>{{ $vGallery->image_url }}</g:additional_image_link>
                            @endforeach
                        @else
                            @foreach($product->galleries as $gallery)
                                <g:additional_image_link>{{ $gallery->image_url }}</g:additional_image_link>
                            @endforeach
                        @endif

                        <g:condition>new</g:condition>
                        <g:availability>{{ $variant->available_stock > 0 ? 'in_stock' : 'out_of_stock' }}</g:availability>
                        <g:price>{{ number_format($variant->regular_price, 2, '.', '') }} {{ $setup->currency ?: 'BDT' }}</g:price>
                        @if($variant->discount > 0)
                            <g:sale_price>{{ number_format($variant->final_price, 2, '.', '') }} {{ $setup->currency ?: 'BDT' }}
                            </g:sale_price>
                        @endif
                        <g:brand>{{ $product->brand->name ?? ($setup->shop_name ?: 'Generic') }}</g:brand>

                        {{-- ভ্যারিয়েন্ট বৈশিষ্ট্য --}}
                        @foreach($variant->attributes as $attr)
                            <g:{{ strtolower($attr->attributeGroup->name ?? 'option') }}>{{ $attr->attributeValue->name ?? '' }}</g:{{ strtolower($attr->attributeGroup->name ?? 'option') }}>
                        @endforeach

                        <g:identifier_exists>no</g:identifier_exists>
                        <g:google_product_category>{{ $product->mega_categories->first()->name ?? 'General' }}
                        </g:google_product_category>
                    </item>
                @endforeach
            @endif
        @endforeach
    </channel>
</rss>
