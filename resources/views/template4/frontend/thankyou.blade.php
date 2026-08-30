@extends('template4.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.checkout-meta', ['setup' => $setup ?? null])
@endsection

@section('content')
    @include('components.frontend.thankyou-content', [
        'order' => $order, 
        'relatedProducts' => $relatedProducts,
        'templatePrefix' => 'template4'
    ])
@endsection
