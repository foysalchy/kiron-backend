<?php
$html = file_get_contents('c:/laragon/www/kiron-backend/resources/views/template3/partials/header.blade.php');
preg_match_all('/<div[^>]*>/i', $html, $opens);
preg_match_all('/<\/div>/i', $html, $closes);
echo 'Template3 header Div opens: ' . count($opens[0]) . "\n";
echo 'Template3 header Div closes: ' . count($closes[0]) . "\n";

$html2 = file_get_contents('c:/laragon/www/kiron-backend/resources/views/template5/partials/header.blade.php');
preg_match_all('/<div[^>]*>/i', $html2, $opens2);
preg_match_all('/<\/div>/i', $html2, $closes2);
echo 'Template5 header Div opens: ' . count($opens2[0]) . "\n";
echo 'Template5 header Div closes: ' . count($closes2[0]) . "\n";
