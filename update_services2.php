<?php
$services = ['Area', 'Rack', 'Cell', 'Bin'];
foreach ($services as $c) {
  $f = "c:/laragon/www/kiron-backend/app/Services/{$c}Service.php";
  if (!file_exists($f)) continue;
  $cnt = file_get_contents($f);
  if (strpos($cnt, "if (!empty(\$filters['select']))") === false) {
    $insertStr = "\n            if (!empty(\$filters['select'])) {\n                \$selectArray = is_string(\$filters['select']) ? explode(',', \$filters['select']) : \$filters['select'];\n                \$query->select(\$selectArray);\n            }\n\n            if (!empty(\$filters['with'])) {\n                \$query->with(\$filters['with']);\n            }\n";
    
    // Replace after the first $query = ...; assignment inside the try block
    $cnt = preg_replace('/(\$query\s*=\s*[A-Za-z]+::[A-Za-z]+\(.*?\);)/', "$1" . $insertStr, $cnt, 1);
    file_put_contents($f, $cnt);
  }
}
echo 'Done';
