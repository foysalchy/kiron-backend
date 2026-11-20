@extends('template2.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.checkout-meta', ['setup' => $setup ?? null])
@endsection

@section('content')
    @include('components.frontend.already-placed-content', [
        'order' => $order, 
        'setup' => $setup,
        'relatedProducts' => $relatedProducts,
        'templatePrefix' => 'template2'
    ])
@endsection
