<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Preview</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: {{ $themeColor->theme_template['primary_color'] ?? '#13565e' }};
            --primary-text: {{ $themeColor->theme_template['primary_text_color'] ?? '#ffffff' }};
            --primary-hover-color: {{ $themeColor->theme_template['primary_hover_color'] ?? '#0d3d43' }};
            --primary-hover-text: {{ $themeColor->theme_template['primary_hover_text'] ?? '#ffffff' }};
            --secondary-color: {{ $themeColor->theme_template['secondary_color'] ?? '#f3f4f6' }};
            --secondary-text: {{ $themeColor->theme_template['secondary_text_color'] ?? '#1f2937' }};
            --header-bg: {{ $themeColor->theme_template['header_color'] ?? '#ffffff' }};
            --header-text: {{ $themeColor->theme_template['header_text_color'] ?? '#1f2937' }};
            --footer-bg: {{ $themeColor->theme_template['footer_color'] ?? '#1f2937' }};
            --footer-text: {{ $themeColor->theme_template['footer_text_color'] ?? '#ffffff' }};
        }

        .primary-bg {
            background-color: var(--primary-color) !important;
            color: var(--primary-text) !important;
        }

        .text-primary {
            color: var(--primary-text) !important;
        }

        .text-brand {
            color: var(--primary-color) !important;
        }

        .secondary-bg {
            background-color: var(--secondary-color) !important;
            color: var(--secondary-text) !important;
        }

        .text-secondary {
            color: var(--secondary-text) !important;
        }

        .hover-text:hover {
            color: var(--primary-hover-text) !important;
        }

        .primary-bg-hover:hover {
            background-color: var(--primary-hover-color) !important;
        }

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

            $componentName = 'template1'. '.product-card';
        @endphp
        <x-dynamic-component :component="$componentName" :product="$product" />
    </div>
</body>
</html>
