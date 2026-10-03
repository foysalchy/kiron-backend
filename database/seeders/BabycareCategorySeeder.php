<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MegaCategory;
use App\Models\SubCategory;
use App\Models\MiniCategory;
use Illuminate\Support\Str;

class BabycareCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = 34;

        $categoriesData = [
            [
                'name' => 'Baby Food',
                'subCategories' => [
                    [
                        'name' => 'Milks',
                        'miniCategories' => ['Almarai', 'Aptamil', 'Cow & Gate', 'ELDOBABY', 'Similac', 'SMA', 'Lactogen', 'Nido', 'NAN', 'Cowhead', 'Ensure', 'Kendamil', 'Biomil', 'PediaSure']
                    ],
                    [
                        'name' => 'Cereals',
                        'miniCategories' => ['Cowhead', 'Quaker', 'ELDOBABY', 'Nestlé', 'Cow and gate', 'Aptamil', 'Heinz', 'Gerber', 'NHF', 'Kelloggs', 'Organix']
                    ],
                    [
                        'name' => 'Nutrition',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Cheese',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Noodles',
                        'miniCategories' => []
                    ]
                ]
            ],
            [
                'name' => 'Feeding',
                'subCategories' => [
                    [
                        'name' => 'Feeder Bottle',
                        'miniCategories' => ['Philips Avent', 'Pur', 'Pigeon', 'Tommee Tippee', 'Aiwibi', 'Fisher-Price']
                    ],
                    [
                        'name' => 'Feeder Nipple',
                        'miniCategories' => ['Pur', 'Philips Avent', 'Pigeon', 'Tommee Tippee', 'Aiwibi']
                    ],
                    [
                        'name' => 'Feeder Covers',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Baby Bibs',
                        'miniCategories' => ['Fisher-Price', 'Pur', 'Pigeon', 'Aiwibi', 'Others']
                    ],
                    [
                        'name' => 'Soothers & Teethers',
                        'miniCategories' => ['Pur', 'Philips Avent', 'Tommee Tippee', 'Aiwibi']
                    ],
                    [
                        'name' => 'Toddler Cups',
                        'miniCategories' => ['Duck', 'Philips Avent', 'Pur', 'Pigeon']
                    ],
                    [
                        'name' => 'Bottle Warmer',
                        'miniCategories' => ['Pigeon']
                    ],
                    [
                        'name' => 'Breast Pump',
                        'miniCategories' => ['PUR', 'Smart Care', 'Philips Avent', 'Pigeon']
                    ],
                    [
                        'name' => 'Breast Milk Storage',
                        'miniCategories' => ['Pigeon', 'PUR']
                    ],
                    [
                        'name' => 'Sterilizer & Washers',
                        'miniCategories' => ['Philips Avent', 'Pur']
                    ],
                    [
                        'name' => 'Feeding Cleaning',
                        'miniCategories' => ['Hercules Bear', 'Kodomo', 'Pigeon', 'Aiwibi', 'Pur']
                    ]
                ]
            ],
            [
                'name' => 'School & Kids',
                'subCategories' => [
                    [
                        'name' => 'School Bag',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Tiffin Box',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Water Bottles',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Stationary',
                        'miniCategories' => ['Pencil Box', 'Pencil', 'Eraser', 'Pencil Sharpener', 'Color Pencil', 'Geometry Box', 'Glue Stick']
                    ],
                    [
                        'name' => 'Kids Watch',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Mini Fan',
                        'miniCategories' => []
                    ]
                ]
            ],
            [
                'name' => 'Grooming & Care',
                'subCategories' => [
                    [
                        'name' => 'Baby Bath',
                        'miniCategories' => ['Johnson\'s', 'Mothercare', 'Boots', 'Cerave', 'Aveeno', 'Smart Care', 'Kodomo', 'Babi Mild']
                    ],
                    [
                        'name' => 'Baby Shampoo',
                        'miniCategories' => ['Johnson\'s', 'Mothercare', 'Boots', 'Kodomo', 'Babi Mild']
                    ],
                    [
                        'name' => 'Baby Lotion',
                        'miniCategories' => ['Mothercare', 'Aveeno', 'Cerave', 'Boots', 'Kodomo', 'Babi Mild']
                    ],
                    [
                        'name' => 'Baby Oil',
                        'miniCategories' => ['Johnson', 'Orkide', 'Boots', 'Kodomo']
                    ],
                    [
                        'name' => 'Baby Cream',
                        'miniCategories' => ['Aveeno', 'Joona Baby', 'Mothercare', 'Johnson', 'Sebamed', 'Cerave', 'Kodomo', 'Babi Mild']
                    ],
                    [
                        'name' => 'Baby Powder',
                        'miniCategories' => ['Johnson', 'Kodomo', 'Babi Mild']
                    ],
                    [
                        'name' => 'Baby Face Wash',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Baby Toothpaste',
                        'miniCategories' => ['Aquafresh', 'Kodomo', 'Babi Mild']
                    ],
                    [
                        'name' => 'Baby Toothbrush',
                        'miniCategories' => ['Fisher-Price', 'Kodomo', 'Pigeon', 'Pur']
                    ],
                    [
                        'name' => 'Baby Nail Clipper',
                        'miniCategories' => ['Pur', 'Pigeon']
                    ],
                    [
                        'name' => 'Nasal Aspirator',
                        'miniCategories' => ['Pur', 'Pigeon']
                    ],
                    [
                        'name' => 'Baby Robes & Mats',
                        'miniCategories' => ['Duck', 'Pigeon']
                    ],
                    [
                        'name' => 'Baby Towel',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Booster Seats & High Chairs',
                        'miniCategories' => ['Smart Care']
                    ],
                    [
                        'name' => 'Rash Cream',
                        'miniCategories' => ['Aveeno', 'Sebamed', 'Sudocrem']
                    ],
                    [
                        'name' => 'Baby Socks',
                        'miniCategories' => []
                    ]
                ]
            ],
            [
                'name' => 'Toys & Play',
                'subCategories' => [
                    [
                        'name' => 'Baby Rattle',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Building Blocks',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Educational Toys',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Musical Toys',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Soft Toys',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Action Figure',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Doll',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Toy Car',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Kaleidoscope',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Puzzles',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Baby Swimming Pool',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Card Game',
                        'miniCategories' => []
                    ]
                ]
            ]
        ];

        foreach ($categoriesData as $megaData) {
            $megaCategory = MegaCategory::updateOrCreate([
                'name' => $megaData['name'],
                'company_id' => $companyId
            ], [
                'slug' => Str::slug($megaData['name']),
                'status' => 1,
                'meta_title' => "{$megaData['name']} Price in Bangladesh",
                'meta_description' => "Shop {$megaData['name']} products in Bangladesh at Littlebaby, including quality {$megaData['name']} products for babies and families. Explore authentic {$megaData['name']} products at competitive prices with convenient online shopping and delivery.",
                'meta_keywords' => ["{$megaData['name']}", "Buy {$megaData['name']}", "{$megaData['name']} Price in BD", "{$megaData['name']} Bangladesh"]
            ]);

            foreach ($megaData['subCategories'] as $subData) {
                $subCategory = SubCategory::updateOrCreate([
                    'mega_category_id' => $megaCategory->id,
                    'name' => $subData['name'],
                    'company_id' => $companyId
                ], [
                    'slug' => Str::slug($subData['name']),
                    'status' => 1,
                    'meta_title' => "{$subData['name']} Price in Bangladesh",
                    'meta_description' => "Shop {$subData['name']} products in Bangladesh at Littlebaby, including quality {$subData['name']} products for babies and families. Explore authentic {$subData['name']} products at competitive prices with convenient online shopping and delivery.",
                    'meta_keywords' => ["{$subData['name']}", "Buy {$subData['name']}", "{$subData['name']} Price in BD", "{$subData['name']} Bangladesh"]
                ]);

                foreach ($subData['miniCategories'] as $miniName) {
                    MiniCategory::updateOrCreate([
                        'mega_category_id' => $megaCategory->id,
                        'sub_category_id' => $subCategory->id,
                        'name' => $miniName,
                        'company_id' => $companyId
                    ], [
                        'slug' => Str::slug($miniName),
                        'status' => 1,
                        'meta_title' => "{$miniName} Price in Bangladesh",
                        'meta_description' => "Shop {$miniName} products in Bangladesh at Littlebaby, including quality {$miniName} products for babies and families. Explore authentic {$miniName} products at competitive prices with convenient online shopping and delivery.",
                        'meta_keywords' => ["{$miniName}", "Buy {$miniName}", "{$miniName} Price in BD", "{$miniName} Bangladesh"]
                    ]);
                }
            }
        }
    }
}
