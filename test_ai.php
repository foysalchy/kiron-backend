<?php
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\AI\AiService;

$params = [
    'type' => 'meta_title',
    'title' => 'AOC AGON PRO AG276FK 27" FHD Fast IPS Gaming Monitor',
    'type_info' => 'single',
    'purpose' => 'For gaming'
];

echo AiService::generate(1, $params);
