<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Preview</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            background-color: transparent;
            overflow: hidden; /* Hide scrollbars */
        }
        .card-container {
            width: 100%;
            max-width: 250px; /* Standard product card size */
            transform: scale(0.85);
            transform-origin: top center;
        }
    </style>
</head>
<body>
    <div class="card-container">
        @php
            if (!isset($themeColor) || is_null($themeColor)) {
                $themeColor = new \stdClass();
                $themeColor->theme_template = [];
            }
            if (!isset($storeSettings) || is_null($storeSettings)) {
                $storeSettings = new \stdClass();
                $storeSettings->is_review = false;
            }
            $templateName = request('preview_card') ? 'template' . request('preview_card') : ($template ?? 'template1');
            $componentName = $templateName . '.product-card';
        @endphp
        <x-dynamic-component :component="$componentName" :product="$product" />
    </div>
</body>
</html>
