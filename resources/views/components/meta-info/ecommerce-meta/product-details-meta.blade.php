@include('components.meta-info.meta',[
    'setup'=>$setup,

    'type'=>'Product',

    'title'=>$product->meta_title ?: $product->name,

    'description'=>$product->meta_description,

    'keywords'=>$product->meta_keywords,

    'image'=>$product->thumbnail,

    'canonical'=>route('product.details',$product->slug),

    'breadcrumb'=>[
        [
            'name'=>'Home',
            'url'=>url('/')
        ],
        [
            'name'=>'Products',
            'url'=>route('product.index')
        ],
        [
            'name'=>$product->name,
            'url'=>route('product.details',$product->slug)
        ]
    ],

    'schema'=>[
        'name'=>$product->title,
        'sku'=>$product->sku_code,
        'price'=>$product->regular_price,
        'currency'=>'BDT',
        'availability'=>'https://schema.org/InStock'
    ]
])
