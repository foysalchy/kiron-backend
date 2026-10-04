<?php
$controllers = ['Warehouse', 'Area', 'Rack', 'Cell', 'Bin'];
foreach ($controllers as $c) {
  $f = "c:/laragon/www/kiron-backend/app/Http/Controllers/Api/{$c}Controller.php";
  if (!file_exists($f)) continue;
  $cnt = file_get_contents($f);
  if (strpos($cnt, "'select'") === false) {
    $cnt = preg_replace('/(\'status\'\s*=>\s*\$request->query\(\'status\'\),)/', "'select'     => \$request->query('select'),\n            'with'       => \$request->query('with'),\n            $1", $cnt);
    file_put_contents($f, $cnt);
  }
}
echo 'Done';
