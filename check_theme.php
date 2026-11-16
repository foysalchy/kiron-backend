<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = App\Models\SiteSetting::find(2);
echo "Theme / Template: " . ($s->theme ?? $s->template ?? 'none') . "\n";
$domain = App\Models\DomainSetup::where('company_id', 1)->first();
echo "Domain: " . ($domain ? $domain->sub_domain : 'none') . " | Theme: " . ($domain->theme ?? 'none') . "\n";
