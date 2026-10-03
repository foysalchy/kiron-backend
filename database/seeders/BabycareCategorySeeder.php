<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MegaCategory;
use App\Models\SubCategory;
use App\Models\MiniCategory;
use Illuminate\Support\Str;

class BabycareCategorySeeder extends Seeder
{
    private function generateSeoDescription($name, $brands = [], $megaCategoryName = '')
    {
        $brandsText = "";
        if (!empty($brands)) {
            $brandsList = implode(', ', array_map(function ($brand) {
                $slug = Str::slug($brand);
                return "<a href=\"https://www.littlebaby.com.bd/{$slug}\">{$brand}</a>";
            }, $brands));
            $brandsText = "<h3>Trusted {$name} Brands in Bangladesh</h3>\n<p>Parents in Bangladesh rely on well-known and trusted brands such as {$brandsList} for safe and high-quality products. These internationally recognized brands follow strict quality and safety standards to ensure the best for your child.</p>\n";
        }

        $slug = Str::slug($name);

        $context = [
            'intro' => "ensuring proper care during the early years is a top priority",
            'role' => "plays a vital role in supporting your child's well-being, health, and development",
            'why' => "provide essential care, comfort, and safety",
            'benefits' => [
                "Supports daily care and hygiene",
                "Ensures maximum safety and comfort for your baby",
                "Trusted and recommended by parents and experts",
                "Made with baby-friendly materials and ingredients",
                "Provides complete peace of mind for parents"
            ]
        ];

        if ($megaCategoryName === 'Baby Food') {
            $context['intro'] = "ensuring proper nutrition during the early years is a top priority";
            $context['role'] = "plays a vital role in supporting healthy growth, immunity, and brain development";
            $context['why'] = "provide essential nutrients, energy, and a balanced diet for growing babies";
            $context['benefits'] = [
                "Supports brain development and cognitive growth",
                "Strengthens bones and teeth with essential minerals",
                "Improves digestion with age-appropriate ingredients",
                "Boosts immunity with essential vitamins",
                "Provides complete and balanced daily nutrition"
            ];
        } elseif ($megaCategoryName === 'Feeding') {
            $context['intro'] = "ensuring a smooth and safe feeding experience is a top priority";
            $context['role'] = "plays a vital role in ensuring your baby feeds comfortably and securely";
            $context['why'] = "reduce colic, improve feeding posture, and make mealtime stress-free for both parents and babies";
            $context['benefits'] = [
                "Anti-colic and ergonomic designs for easy feeding",
                "Made from BPA-free and food-grade safe materials",
                "Easy to clean, assemble, and sterilize",
                "Helps transition from breastfeeding to bottle or solid feeding smoothly",
                "Durable and trusted by parents globally"
            ];
        } elseif ($megaCategoryName === 'Grooming & Care') {
            $context['intro'] = "maintaining daily hygiene and delicate skin care is a top priority";
            $context['role'] = "plays a vital role in keeping your baby clean, fresh, and free from rashes or irritations";
            $context['why'] = "provide gentle, tear-free, and hypoallergenic care for your baby's sensitive skin";
            $context['benefits'] = [
                "Gentle on sensitive and delicate baby skin",
                "Tear-free and hypoallergenic formulas",
                "Maintains natural skin moisture and prevents dryness",
                "Free from harmful chemicals, parabens, and dyes",
                "Dermatologically tested and pediatrician recommended"
            ];
        } elseif ($megaCategoryName === 'School & Kids') {
            $context['intro'] = "providing the right educational tools and accessories is a top priority for growing kids";
            $context['role'] = "plays a vital role in keeping children organized, enthusiastic, and ready to learn";
            $context['why'] = "make school days more fun, organized, and productive with kid-friendly designs";
            $context['benefits'] = [
                "Durable and long-lasting materials for daily use",
                "Lightweight and ergonomic designs suitable for kids",
                "Features vibrant colors and favorite characters",
                "Encourages independence and organization skills",
                "Safe, non-toxic materials for everyday use"
            ];
        } elseif ($megaCategoryName === 'Toys & Play') {
            $context['intro'] = "encouraging learning through play is a top priority for early childhood development";
            $context['role'] = "plays a vital role in stimulating creativity, motor skills, and cognitive development";
            $context['why'] = "engage your child’s imagination and provide hours of safe, educational entertainment";
            $context['benefits'] = [
                "Enhances fine and gross motor skills",
                "Stimulates brain development and problem-solving abilities",
                "Made from non-toxic, child-safe, and durable materials",
                "Encourages independent and cooperative play",
                "Provides screen-free entertainment and joyful learning"
            ];
        }

        $benefitsHtml = implode("\n", array_map(fn($b) => "<li>{$b}</li>", $context['benefits']));

        return "<h2>{$name} in Bangladesh: Essential Care for Your Little One</h2>
<p>For parents in Bangladesh, {$context['intro']}. {$name} {$context['role']}. With trusted products available from leading brands, parents can confidently provide their little ones with safe and reliable solutions that meet international standards. At Littlebaby, families can access a wide range of certified {$name} products, delivered conveniently to their doorstep, making healthy choices easier and more reliable every day.</p>

<h3>Why {$name} Is Important</h3>
<p><a href=\"https://www.littlebaby.com.bd/{$slug}\">{$name}</a> products are specially designed to {$context['why']}. These products support your baby's daily needs, ensuring they remain healthy and happy. Choosing the right items helps ensure optimal care as your child grows and their needs evolve.</p>

{$brandsText}
<h3>Benefits of Choosing the Right {$name}</h3>
<ul>
{$benefitsHtml}
</ul>

<h3>Buy {$name} Online from Littlebaby</h3>
<p>Shopping for {$name} online from <a href=\"https://www.littlebaby.com.bd/\">Littlebaby</a> ensures access to genuine products, detailed information, and convenient home delivery. Parents can confidently choose trusted {$name} knowing their child’s safety is prioritized.</p>";
    }

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
            $allMiniBrandsForMega = [];
            foreach ($megaData['subCategories'] as $sub) {
                $allMiniBrandsForMega = array_merge($allMiniBrandsForMega, $sub['miniCategories']);
            }
            $allMiniBrandsForMega = array_unique($allMiniBrandsForMega);

            $megaCategory = MegaCategory::updateOrCreate([
                'name' => $megaData['name'],
                'company_id' => $companyId
            ], [
                'slug' => Str::slug($megaData['name']),
                'status' => 1,
                'description' => $this->generateSeoDescription($megaData['name'], array_slice($allMiniBrandsForMega, 0, 10), $megaData['name']),
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
                    'description' => $this->generateSeoDescription($subData['name'], array_slice($subData['miniCategories'], 0, 10), $megaData['name']),
                    'meta_title' => "{$subData['name']} Price in Bangladesh",
                    'meta_description' => "Shop {$subData['name']} products in Bangladesh at Littlebaby, including quality {$subData['name']} products for babies and families. Explore authentic {$subData['name']} products at competitive prices with convenient online shopping and delivery.",
                    'meta_keywords' => ["{$subData['name']}", "Buy {$subData['name']}", "{$subData['name']} Price in BD", "{$subData['name']} Bangladesh"]
                ]);

                foreach ($subData['miniCategories'] as $miniName) {
                    $combinedName = "{$miniName} {$subData['name']}";
                    MiniCategory::updateOrCreate([
                        'mega_category_id' => $megaCategory->id,
                        'sub_category_id' => $subCategory->id,
                        'name' => $miniName,
                        'company_id' => $companyId
                    ], [
                        'slug' => Str::slug($miniName),
                        'status' => 1,
                        'description' => $this->generateSeoDescription($combinedName, [], $megaData['name']),
                        'meta_title' => "{$combinedName} Price in Bangladesh",
                        'meta_description' => "Shop {$miniName} products in Bangladesh at Littlebaby, including quality {$miniName} products for babies and families. Explore authentic {$miniName} products at competitive prices with convenient online shopping and delivery.",
                        'meta_keywords' => ["{$miniName}", "Buy {$miniName}", "{$miniName} Price in BD", "{$miniName} Bangladesh"]
                    ]);
                }
            }
        }
    }
}
