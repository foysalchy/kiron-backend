const fs = require('fs');
let code = fs.readFileSync('c:/laragon/www/kiron-backend/app/Services/ProductService.php', 'utf8');

code = code.replace(
  /\$data\['thumbnail'\] = FileUploadHelper::uploadImage\(\n?\s*\$imageFile,\n?\s*'products\/thumbnails',\n?\s*'r2',\n?\s*2048,\n?\s*\$customFileName\n?\s*\);/g,
  `$data['thumbnail'] = FileUploadHelper::uploadImage(
                    $imageFile,
                    'products/thumbnails',
                    'r2',
                    2048,
                    $customFileName,
                    true
                );`
);

code = code.replace(
  /\$data\['thumbnail_310'\] = FileUploadHelper::uploadResizedWebpImage\(\n?\s*\$imageFile,\n?\s*'products\/thumbnails',\n?\s*310,\n?\s*310,\n?\s*'r2',\n?\s*\$thumbnail310FileName\n?\s*\);/g,
  `$data['thumbnail_310'] = FileUploadHelper::uploadResizedWebpImage(
                    $imageFile,
                    'products/thumbnails',
                    310,
                    310,
                    'r2',
                    $thumbnail310FileName,
                    true
                );`
);

code = code.replace(
  /\$data\['thumbnail_95'\] = FileUploadHelper::uploadResizedWebpImage\(\n?\s*\$imageFile,\n?\s*'products\/thumbnails',\n?\s*95,\n?\s*95,\n?\s*'r2',\n?\s*\$thumbnail95FileName\n?\s*\);/g,
  `$data['thumbnail_95'] = FileUploadHelper::uploadResizedWebpImage(
                    $imageFile,
                    'products/thumbnails',
                    95,
                    95,
                    'r2',
                    $thumbnail95FileName,
                    true
                );`
);

code = code.replace(
  /\$imagePath = FileUploadHelper::uploadImage\(\n?\s*\$image,\n?\s*'products\/galleries',\n?\s*'r2',\n?\s*2048,\n?\s*\$customFileName\n?\s*\);/g,
  `$imagePath = FileUploadHelper::uploadImage(
                $image,
                'products/galleries',
                'r2',
                2048,
                $customFileName,
                true
            );`
);

code = code.replace(
  /\$variationData\['image'\] = FileUploadHelper::uploadImage\(\n?\s*\$variationData\['image'\],\n?\s*'products\/variation',\n?\s*'r2',\n?\s*2048,\n?\s*\$customFileName\n?\s*\);/g,
  `$variationData['image'] = FileUploadHelper::uploadImage(
                    $variationData['image'],
                    'products/variation',
                    'r2',
                    2048,
                    $customFileName,
                    true
                );`
);

// For replace methods in updateProduct
code = code.replace(
  /\$data\['thumbnail'\] = FileUploadHelper::replace\(\n?\s*\$imageFile,\n?\s*\$product->thumbnail,\n?\s*'products\/thumbnails',\n?\s*'r2',\n?\s*\$customFileName\n?\s*\);/g,
  `$data['thumbnail'] = FileUploadHelper::replace(
                        $imageFile,
                        $product->thumbnail,
                        'products/thumbnails',
                        'r2',
                        $customFileName,
                        true
                    );`
);

code = code.replace(
  /\$data\['thumbnail_310'\] = FileUploadHelper::replaceResizedWebpImage\(\n?\s*\$imageFile,\n?\s*\$product->thumbnail_310,\n?\s*'products\/thumbnails',\n?\s*310,\n?\s*310,\n?\s*'r2',\n?\s*\$thumbnail310FileName\n?\s*\);/g,
  `$data['thumbnail_310'] = FileUploadHelper::replaceResizedWebpImage(
                        $imageFile,
                        $product->thumbnail_310,
                        'products/thumbnails',
                        310,
                        310,
                        'r2',
                        $thumbnail310FileName,
                        true
                    );`
);

code = code.replace(
  /\$data\['thumbnail_95'\] = FileUploadHelper::replaceResizedWebpImage\(\n?\s*\$imageFile,\n?\s*\$product->thumbnail_95,\n?\s*'products\/thumbnails',\n?\s*95,\n?\s*95,\n?\s*'r2',\n?\s*\$thumbnail95FileName\n?\s*\);/g,
  `$data['thumbnail_95'] = FileUploadHelper::replaceResizedWebpImage(
                        $imageFile,
                        $product->thumbnail_95,
                        'products/thumbnails',
                        95,
                        95,
                        'r2',
                        $thumbnail95FileName,
                        true
                    );`
);

fs.writeFileSync('c:/laragon/www/kiron-backend/app/Services/ProductService.php', code);
console.log('Done updating ProductService.');
