@extends('template2.layouts.front')

@section('content')
    @include('components.frontend.thankyou-content', [
        'order' => $order, 
        'relatedProducts' => $relatedProducts,
        'templatePrefix' => 'template2'
    ])
@endsection
