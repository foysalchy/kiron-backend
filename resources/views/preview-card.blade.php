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
        <x-template1.product-card :product="$product" />
    </div>
</body>
</html>
