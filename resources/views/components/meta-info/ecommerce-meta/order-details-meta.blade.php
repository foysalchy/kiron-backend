@include('components.meta-info.meta', [
    'setup' => $setup,

    'type' => 'WebPage',

    'title' => 'Order Details #' . ($order->order_no ?? $order->id) . ' | ' . ($setup->shop_name ?? 'Bhaiya Digital'),

    'description' => 'View the summary, shipping status, and item details of your order #' . ($order->order_no ?? $order->id) . '.',

    'keywords' => 'order details, order status, purchase history',

    'image' => $setup->logo_url ?? asset('images/default-share-image.jpg'),

    'canonical' => url()->current(),

    'robots' => 'noindex, nofollow',

    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => 'My Account',
            'url' => route('user.dashboard'),
        ],
        [
            'name' => 'Order Details',
            'url' => url()->current(),
        ],
    ],
])
