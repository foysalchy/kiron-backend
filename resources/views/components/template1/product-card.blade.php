@php

    $company = getCurrentCompany();
    $template = $company->product_card_template ?? 1;
@endphp
@if ($template == 1)
    @include('components.template1.product-1', ['product' => $product])
@elseif($template == 2)
    @include('components.template1.product-2', ['product' => $product])
@endif
