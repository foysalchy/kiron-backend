<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = \App\Models\AiSetting::first()->gemini_key;
if (!$key) die('No key found');

$response = Illuminate\Support\Facades\Http::get('https://generativelanguage.googleapis.com/v1beta/models?key=' . $key);

if ($response->failed()) {
    echo "Failed: " . $response->body();
} else {
    $models = collect($response->json('models'))->pluck('name', 'displayName')->toArray();
    echo json_encode($models, JSON_PRETTY_PRINT);
}
