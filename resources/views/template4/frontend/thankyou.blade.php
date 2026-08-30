@extends('template4.layouts.front')

@section('content')
    @include('components.frontend.thankyou-content', [
        'order' => $order, 
        'relatedProducts' => $relatedProducts,
        'templatePrefix' => 'template4'
    ])
@endsection
