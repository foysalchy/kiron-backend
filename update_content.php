<?php
// Controllers to update: add 'select' and 'with' to $filters
$controllers = [
    'Blog'          => 'c:/laragon/www/kiron-backend/app/Http/Controllers/Api/BlogController.php',
    'Page'          => 'c:/laragon/www/kiron-backend/app/Http/Controllers/Api/PageController.php',
    'Slider'        => 'c:/laragon/www/kiron-backend/app/Http/Controllers/Api/SliderController.php',
    'KnowledgeBase' => 'c:/laragon/www/kiron-backend/app/Http/Controllers/Api/KnowledgeBaseController.php',
];

foreach ($controllers as $name => $path) {
    if (!file_exists($path)) { echo "Not found: $path\n"; continue; }
    $cnt = file_get_contents($path);
    if (strpos($cnt, "'select'") !== false) { echo "Already done: $name\n"; continue; }

    // Add select and with before 'status'
    $cnt = preg_replace(
        "/('status'\s*=>\s*\\\$request->query\('status'\),)/",
        "'select'     => \$request->query('select'),\n            'with'       => \$request->query('with'),\n            $1",
        $cnt
    );
    file_put_contents($path, $cnt);
    echo "Controller updated: $name\n";
}

// Services to update: add select/with logic after $query = Model::query();
$services = [
    'Blog'          => ['path' => 'c:/laragon/www/kiron-backend/app/Services/BlogService.php',          'model' => 'Blog'],
    'Page'          => ['path' => 'c:/laragon/www/kiron-backend/app/Services/PageService.php',          'model' => 'Page'],
    'Slider'        => ['path' => 'c:/laragon/www/kiron-backend/app/Services/SliderService.php',        'model' => 'Slider'],
    'KnowledgeBase' => ['path' => 'c:/laragon/www/kiron-backend/app/Services/KnowledgeBaseService.php', 'model' => 'KnowledgeBase'],
];

$insertStr = "\n            if (!empty(\$filters['select'])) {\n                \$selectArray = is_string(\$filters['select']) ? explode(',', \$filters['select']) : \$filters['select'];\n                \$query->select(\$selectArray);\n            }\n\n            if (!empty(\$filters['with'])) {\n                \$query->with(\$filters['with']);\n            }\n";

foreach ($services as $name => $info) {
    if (!file_exists($info['path'])) { echo "Not found: {$info['path']}\n"; continue; }
    $cnt = file_get_contents($info['path']);
    if (strpos($cnt, "if (!empty(\$filters['select']))") !== false) { echo "Already done: $name\n"; continue; }

    // Insert after first $query = Model::query();
    $cnt = preg_replace(
        '/(\$query\s*=\s*' . $info['model'] . '::query\(\);)/',
        "$1" . $insertStr,
        $cnt,
        1
    );
    file_put_contents($info['path'], $cnt);
    echo "Service updated: $name\n";
}

echo "All done.";
