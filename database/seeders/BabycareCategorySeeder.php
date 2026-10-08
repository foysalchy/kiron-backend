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
                'name' => 'Diapering',
                'slug' => 'diapering',
                'subCategories' => [
                    [
                        'name' => 'Diapers',
                        'slug' => 'diapers',
                        'miniCategories' => [
                            ['name' => 'Aiwibi', 'slug' => 'aiwibi-diaper'],
                            ['name' => 'Genkikun', 'slug' => 'genkikun-diaper'],
                            ['name' => 'Huggies', 'slug' => 'huggies-diaper'],
                            ['name' => 'MamyPoko', 'slug' => 'mamypoko-diaper'],
                            ['name' => 'Momotaro', 'slug' => 'momotaro-diaper'],
                            ['name' => 'Mumlove', 'slug' => 'mumlove-diapers'],
                            ['name' => 'NeoCare', 'slug' => 'neocare-diaper'],
                            ['name' => 'Paisoft', 'slug' => 'paisoft-diaper'],
                            ['name' => 'Pampers', 'slug' => 'pampers-diaper'],
                            ['name' => 'Smart Care', 'slug' => 'smart-care-diaper'],
                            ['name' => 'Avonee', 'slug' => 'avonee-diaper'],
                            ['name' => 'Molfix', 'slug' => 'molfix-diaper'],
                            ['name' => 'Babyology', 'slug' => 'babyology-diaper'],
                            ['name' => 'Happy Nappy', 'slug' => 'fresh-diaper'],
                            ['name' => 'SafeNest', 'slug' => 'safenest-diaper'],
                            ['name' => 'Kinder', 'slug' => 'kinder-diaper'],
                            ['name' => 'Twinkle', 'slug' => 'twinkle-diaper'],
                            ['name' => 'Supermom', 'slug' => 'supermom-diaper'],
                            ['name' => 'Kidz', 'slug' => 'kidz-diapers']
                        ]
                    ],
                    [
                        'name' => 'Wipes',
                        'slug' => 'wipes',
                        'miniCategories' => [
                            ['name' => 'Pozzy', 'slug' => 'pozzy-wipes'],
                            ['name' => 'Aiwibi', 'slug' => 'aiwibi-wipes'],
                            ['name' => 'Neocare', 'slug' => 'neocare-wipes'],
                            ['name' => 'Molfix', 'slug' => 'molfix-wipes'],
                            ['name' => 'Supermom', 'slug' => 'supermom-wipes'],
                            ['name' => 'Smart Care', 'slug' => 'smart-care-wipes'],
                            ['name' => 'Avonee', 'slug' => 'avonee-wipes'],
                            ['name' => 'Huggies', 'slug' => 'huggies-wipes'],
                            ['name' => 'Happy Nappy', 'slug' => 'fresh-happy-nappy-wipes'],
                            ['name' => 'Kidz', 'slug' => 'kidz-wipes']
                        ]
                    ],
                    [
                        'name' => 'Diaper Bag & Storages',
                        'slug' => 'diaper-bag-and-storages',
                        'miniCategories' => []
                    ]
                ]
            ],
            [
                'name' => 'Baby Food',
                'slug' => 'baby-food',
                'subCategories' => [
                    [
                        'name' => 'Milks',
                        'slug' => 'milks',
                        'miniCategories' => [
                            ['name' => 'Almarai', 'slug' => 'almarai-powder-milk'],
                            ['name' => 'Aptamil', 'slug' => 'aptamil-milk'],
                            ['name' => 'Cow & Gate', 'slug' => 'cow-and-gate-milk'],
                            ['name' => 'ELDOBABY', 'slug' => 'eldobaby-milk'],
                            ['name' => 'Similac', 'slug' => 'similac-milk'],
                            ['name' => 'SMA', 'slug' => 'sma-milk'],
                            ['name' => 'Lactogen', 'slug' => 'lactogen-milk'],
                            ['name' => 'Nido', 'slug' => 'nido-milk'],
                            ['name' => 'NAN', 'slug' => 'nan-milk'],
                            ['name' => 'Cowhead', 'slug' => 'cowhead-milk'],
                            ['name' => 'Ensure', 'slug' => 'ensure-milk'],
                            ['name' => 'Kendamil', 'slug' => 'kendamil-milk'],
                            ['name' => 'Biomil', 'slug' => 'biomil-milk'],
                            ['name' => 'PediaSure', 'slug' => 'pediasure-milk']
                        ]
                    ],
                    [
                        'name' => 'Cereals',
                        'slug' => 'cereals',
                        'miniCategories' => [
                            ['name' => 'Cowhead', 'slug' => 'cowhead-cereals'],
                            ['name' => 'Quaker', 'slug' => 'quaker-cereals'],
                            ['name' => 'ELDOBABY', 'slug' => 'eldobaby-cereals'],
                            ['name' => 'Nestlé', 'slug' => 'nestle-cereals'],
                            ['name' => 'Cow and gate', 'slug' => 'cow-and-gate-cereal'],
                            ['name' => 'Aptamil', 'slug' => 'aptamil-cereal'],
                            ['name' => 'Heinz', 'slug' => 'heinz-cereal'],
                            ['name' => 'Gerber', 'slug' => 'gerber-cereals'],
                            ['name' => 'NHF', 'slug' => 'nhf-cereal'],
                            ['name' => 'Kelloggs', 'slug' => 'kelloggs-cereal'],
                            ['name' => 'Organix', 'slug' => 'organix-cereal']
                        ]
                    ],
                    [
                        'name' => 'Nutrition',
                        'slug' => 'nutrition',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Cheese',
                        'slug' => 'cheese',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Noodles',
                        'slug' => 'noodles',
                        'miniCategories' => []
                    ]
                ]
            ],
            [
                'name' => 'Feeding',
                'slug' => 'feeding',
                'subCategories' => [
                    [
                        'name' => 'Feeder Bottle',
                        'slug' => 'bottles',
                        'miniCategories' => [
                            ['name' => 'Philips Avent', 'slug' => 'philips-avent-bottle'],
                            ['name' => 'Pur', 'slug' => 'pur-feeding-bottle'],
                            ['name' => 'Pigeon', 'slug' => 'pigeon-feeder-bottle'],
                            ['name' => 'Tommee Tippee', 'slug' => 'tommee-tippee-bottle'],
                            ['name' => 'Aiwibi', 'slug' => 'aiwibi-feeding-bottle'],
                            ['name' => 'Fisher-Price', 'slug' => 'fisher-price-feeder-bottle']
                        ]
                    ],
                    [
                        'name' => 'Feeder Nipple',
                        'slug' => 'feeder-nipple',
                        'miniCategories' => [
                            ['name' => 'Pur', 'slug' => 'pur-nipple'],
                            ['name' => 'Philips Avent', 'slug' => 'philips-avent-nipple'],
                            ['name' => 'Pigeon', 'slug' => 'pigeon-nipple'],
                            ['name' => 'Tommee Tippee', 'slug' => 'tommee-tippee-teat'],
                            ['name' => 'Aiwibi', 'slug' => 'aiwibi-feeder-nipple']
                        ]
                    ],
                    [
                        'name' => 'Feeder Covers',
                        'slug' => 'feeder-covers',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Baby Bibs',
                        'slug' => 'baby-bib',
                        'miniCategories' => [
                            ['name' => 'Fisher-Price', 'slug' => 'fisher-price-bib'],
                            ['name' => 'Pur', 'slug' => 'pur-baby-bib'],
                            ['name' => 'Pigeon', 'slug' => 'pigeon-bib'],
                            ['name' => 'Aiwibi', 'slug' => 'aiwibi-bibs'],
                            ['name' => 'Others', 'slug' => 'other-brands-baby-bibs']
                        ]
                    ],
                    [
                        'name' => 'Soothers & Teethers',
                        'slug' => 'soothers-teethers',
                        'miniCategories' => [
                            ['name' => 'Pur', 'slug' => 'pur-soother-teether'],
                            ['name' => 'Philips Avent', 'slug' => 'philips-avent-soothers-teethers'],
                            ['name' => 'Tommee Tippee', 'slug' => 'tommee-tippee-soother'],
                            ['name' => 'Aiwibi', 'slug' => 'aiwibi-soothers-teethers']
                        ]
                    ],
                    [
                        'name' => 'Toddler Cups',
                        'slug' => 'toddler-cups',
                        'miniCategories' => [
                            ['name' => 'Duck', 'slug' => 'duck-toddler-cups'],
                            ['name' => 'Philips Avent', 'slug' => 'philips-avent-toddler-cups'],
                            ['name' => 'Pur', 'slug' => 'pur-toddler-cups'],
                            ['name' => 'Pigeon', 'slug' => 'pigeon-toddler-cup']
                        ]
                    ],
                    [
                        'name' => 'Bottle Warmer',
                        'slug' => 'food-blenders-steamers-and-bottle-warmers',
                        'miniCategories' => [
                            ['name' => 'Pigeon', 'slug' => 'pigeon-bottle-warmer']
                        ]
                    ],
                    [
                        'name' => 'Breast Pump',
                        'slug' => 'breast-pumps',
                        'miniCategories' => [
                            ['name' => 'PUR', 'slug' => 'pur-breast-pump'],
                            ['name' => 'Smart Care', 'slug' => 'smart-care-breast-pump'],
                            ['name' => 'Philips Avent', 'slug' => 'philips-avent-breast-pump'],
                            ['name' => 'Pigeon', 'slug' => 'pigeon-breast-pump']
                        ]
                    ],
                    [
                        'name' => 'Breast Milk Storage',
                        'slug' => 'breast-milk-storage',
                        'miniCategories' => [
                            ['name' => 'Pigeon', 'slug' => 'pigeon-breast-milk-storage'],
                            ['name' => 'PUR', 'slug' => 'pur-breast-milk-storage']
                        ]
                    ],
                    [
                        'name' => 'Sterilizer & Washers',
                        'slug' => 'sterilizer-washers',
                        'miniCategories' => [
                            ['name' => 'Philips Avent', 'slug' => 'philips-avent-sterilizer-washers'],
                            ['name' => 'Pur', 'slug' => 'pur-sterilizer-washers']
                        ]
                    ],
                    [
                        'name' => 'Feeding Cleaning',
                        'slug' => 'feeding-cleaning',
                        'miniCategories' => [
                            ['name' => 'Hercules Bear', 'slug' => 'hercules-bear-feeding-cleaning-products'],
                            ['name' => 'Kodomo', 'slug' => 'kodomo-feeding-cleaning'],
                            ['name' => 'Pigeon', 'slug' => 'pigeon-feeding-cleaning'],
                            ['name' => 'Aiwibi', 'slug' => 'aiwibi-feeding-cleaning'],
                            ['name' => 'Pur', 'slug' => 'pur-feeding-cleaning']
                        ]
                    ]
                ]
            ],
            [
                'name' => 'School & Kids',
                'slug' => 'school',
                'subCategories' => [
                    [
                        'name' => 'School Bag',
                        'slug' => 'backpacks-school-bags',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Tiffin Box',
                        'slug' => 'tiffin-box',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Water Bottles',
                        'slug' => 'water-bottles',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Stationary',
                        'slug' => 'stationary',
                        'miniCategories' => [
                            ['name' => 'Pencil Box', 'slug' => 'pencil-box'],
                            ['name' => 'Pencil', 'slug' => 'pencil'],
                            ['name' => 'Eraser', 'slug' => 'eraser'],
                            ['name' => 'Pencil Sharpener', 'slug' => 'pencil-sharpener'],
                            ['name' => 'Color Pencil', 'slug' => 'color-pencil'],
                            ['name' => 'Geometry Box', 'slug' => 'geometry-box'],
                            ['name' => 'Glue Stick', 'slug' => 'glue-stick']
                        ]
                    ],
                    [
                        'name' => 'Kids Watch',
                        'slug' => 'kids-watch',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Mini Fan',
                        'slug' => 'mini-fan',
                        'miniCategories' => []
                    ]
                ]
            ],
            [
                'name' => 'Grooming & Care',
                'slug' => 'grooming-care',
                'subCategories' => [
                    [
                        'name' => 'Baby Bath',
                        'slug' => 'bathing',
                        'miniCategories' => [
                            ['name' => 'Johnson\'s', 'slug' => 'johnsons-baby-bath'],
                            ['name' => 'Mothercare', 'slug' => 'mothercare-baby-bath'],
                            ['name' => 'Boots', 'slug' => 'boots-baby-bath'],
                            ['name' => 'Cerave', 'slug' => 'cerave-baby-bath'],
                            ['name' => 'Aveeno', 'slug' => 'aveeno-baby-bath'],
                            ['name' => 'Smart Care', 'slug' => 'smart-care-baby-bath'],
                            ['name' => 'Kodomo', 'slug' => 'kodomo-baby-bath'],
                            ['name' => 'Babi Mild', 'slug' => 'babi-mild-baby-bath']
                        ]
                    ],
                    [
                        'name' => 'Baby Shampoo',
                        'slug' => 'baby-shampoo',
                        'miniCategories' => [
                            ['name' => 'Johnson\'s', 'slug' => 'johnsons-baby-shampoo'],
                            ['name' => 'Mothercare', 'slug' => 'mothercare-shampoo'],
                            ['name' => 'Boots', 'slug' => 'boots-shampoo'],
                            ['name' => 'Kodomo', 'slug' => 'kodomo-shampoo'],
                            ['name' => 'Babi Mild', 'slug' => 'babi-mild-baby-shampoo']
                        ]
                    ],
                    [
                        'name' => 'Baby Lotion',
                        'slug' => 'lotions-oils',
                        'miniCategories' => [
                            ['name' => 'Mothercare', 'slug' => 'mothercare-baby-lotion'],
                            ['name' => 'Aveeno', 'slug' => 'aveeno-lotion'],
                            ['name' => 'Cerave', 'slug' => 'cerave-lotion'],
                            ['name' => 'Boots', 'slug' => 'boots-lotion'],
                            ['name' => 'Kodomo', 'slug' => 'kodomo-lotion'],
                            ['name' => 'Babi Mild', 'slug' => 'babi-mild-baby-lotion']
                        ]
                    ],
                    [
                        'name' => 'Baby Oil',
                        'slug' => 'baby-oil',
                        'miniCategories' => [
                            ['name' => 'Johnson', 'slug' => 'johnson-baby-oil'],
                            ['name' => 'Orkide', 'slug' => 'orkide-oil'],
                            ['name' => 'Boots', 'slug' => 'boots-oil'],
                            ['name' => 'Kodomo', 'slug' => 'kodomo-oil']
                        ]
                    ],
                    [
                        'name' => 'Baby Cream',
                        'slug' => 'baby-cream',
                        'miniCategories' => [
                            ['name' => 'Aveeno', 'slug' => 'aveeno-baby-cream'],
                            ['name' => 'Joona Baby', 'slug' => 'joona-baby-sunscreen'],
                            ['name' => 'Mothercare', 'slug' => 'mothercare-cream'],
                            ['name' => 'Johnson', 'slug' => 'johnson-baby-cream'],
                            ['name' => 'Sebamed', 'slug' => 'sebamed-baby-cream'],
                            ['name' => 'Cerave', 'slug' => 'cerave-baby-cream'],
                            ['name' => 'Kodomo', 'slug' => 'kodomo-cream'],
                            ['name' => 'Babi Mild', 'slug' => 'babi-mild-baby-cream']
                        ]
                    ],
                    [
                        'name' => 'Baby Powder',
                        'slug' => 'baby-powder-baby-cream',
                        'miniCategories' => [
                            ['name' => 'Johnson', 'slug' => 'johnsons-baby-powder'],
                            ['name' => 'Kodomo', 'slug' => 'kodomo-baby-powder'],
                            ['name' => 'Babi Mild', 'slug' => 'babi-mild-baby-powder']
                        ]
                    ],
                    [
                        'name' => 'Baby Face Wash',
                        'slug' => 'baby-face-wash',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Baby Toothpaste',
                        'slug' => 'toothpastes',
                        'miniCategories' => [
                            ['name' => 'Aquafresh', 'slug' => 'aquafresh-toothpaste'],
                            ['name' => 'Kodomo', 'slug' => 'kodomo-toothpaste'],
                            ['name' => 'Babi Mild', 'slug' => 'babi-mild-kids-toothpaste']
                        ]
                    ],
                    [
                        'name' => 'Baby Toothbrush',
                        'slug' => 'toothbrushe',
                        'miniCategories' => [
                            ['name' => 'Fisher-Price', 'slug' => 'fisher-price-toothbrush'],
                            ['name' => 'Kodomo', 'slug' => 'kodomo-toothbrush'],
                            ['name' => 'Pigeon', 'slug' => 'pigeon-toothbrush'],
                            ['name' => 'Pur', 'slug' => 'pur-toothbrush']
                        ]
                    ],
                    [
                        'name' => 'Baby Nail Clipper',
                        'slug' => 'baby-nail-clipper',
                        'miniCategories' => [
                            ['name' => 'Pur', 'slug' => 'pur-baby-nail-clipper'],
                            ['name' => 'Pigeon', 'slug' => 'pigeon-nail-clipper']
                        ]
                    ],
                    [
                        'name' => 'Nasal Aspirator',
                        'slug' => 'nasal-aspirator',
                        'miniCategories' => [
                            ['name' => 'Pur', 'slug' => 'pur-nasal-aspirator'],
                            ['name' => 'Pigeon', 'slug' => 'pigeon-nasal-aspirator']
                        ]
                    ],
                    [
                        'name' => 'Baby Robes & Mats',
                        'slug' => 'baby-robes-and-mats',
                        'miniCategories' => [
                            ['name' => 'Duck', 'slug' => 'duck-baby-robes-and-mats'],
                            ['name' => 'Pigeon', 'slug' => 'pigeon-table-mat']
                        ]
                    ],
                    [
                        'name' => 'Baby Towel',
                        'slug' => 'baby-towel',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Booster Seats & High Chairs',
                        'slug' => 'booster-seats-and-high-chairs',
                        'miniCategories' => [
                            ['name' => 'Smart Care', 'slug' => 'smart-care-booster-seats-and-high-chairs']
                        ]
                    ],
                    [
                        'name' => 'Rash Cream',
                        'slug' => 'rash-cream-and-ointment',
                        'miniCategories' => [
                            ['name' => 'Aveeno', 'slug' => 'aveeno-rash-cream'],
                            ['name' => 'Sebamed', 'slug' => 'sebamed-rash-cream'],
                            ['name' => 'Sudocrem', 'slug' => 'sudocrem-rash-cream']
                        ]
                    ],
                    [
                        'name' => 'Baby Socks',
                        'slug' => 'socks',
                        'miniCategories' => []
                    ]
                ]
            ],
            [
                'name' => 'Toys & Play',
                'slug' => 'toys-and-play',
                'subCategories' => [
                    [
                        'name' => 'Baby Rattle',
                        'slug' => 'baby-rattle-toy',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Building Blocks',
                        'slug' => 'building-block',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Educational Toys',
                        'slug' => 'educational-toy',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Musical Toys',
                        'slug' => 'musical-toys',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Soft Toys',
                        'slug' => 'soft-toys',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Action Figure',
                        'slug' => 'action-figure',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Doll',
                        'slug' => 'doll',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Toy Car',
                        'slug' => 'toy-car',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Kaleidoscope',
                        'slug' => 'kaleidoscope-toys',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Puzzles',
                        'slug' => 'puzzles',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Baby Swimming Pool',
                        'slug' => 'swimming-pool',
                        'miniCategories' => []
                    ],
                    [
                        'name' => 'Card Game',
                        'slug' => 'card-game',
                        'miniCategories' => []
                    ]
                ]
            ]
        ];

        // Sort subCategories and miniCategories alphabetically before insertion
        foreach ($categoriesData as &$megaData) {
            usort($megaData['subCategories'], function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            foreach ($megaData['subCategories'] as &$subData) {
                usort($subData['miniCategories'], function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
            }
        }
        unset($megaData, $subData); // clean up references

        foreach ($categoriesData as $megaData) {
            $allMiniBrandsForMega = [];
            foreach ($megaData['subCategories'] as $sub) {
                foreach ($sub['miniCategories'] as $mini) {
                    $allMiniBrandsForMega[] = $mini['name'];
                }
            }
            $allMiniBrandsForMega = array_unique($allMiniBrandsForMega);

            $megaCategory = MegaCategory::where('name', $megaData['name'])->where('company_id', $companyId)->first();
            if (!$megaCategory) {
                $megaCategory = MegaCategory::create([
                    'name' => $megaData['name'],
                    'company_id' => $companyId,
                    'slug' => $megaData['slug'],
                    'status' => 1,
                    'description' => $this->generateSeoDescription($megaData['name'], array_slice($allMiniBrandsForMega, 0, 10), $megaData['name']),
                    'meta_title' => "{$megaData['name']} Price in Bangladesh",
                    'meta_description' => "Shop {$megaData['name']} products in Bangladesh at Littlebaby, including quality {$megaData['name']} products for babies and families. Explore authentic {$megaData['name']} products at competitive prices with convenient online shopping and delivery.",
                    'meta_keywords' => ["{$megaData['name']}", "Buy {$megaData['name']}", "{$megaData['name']} Price in BD", "{$megaData['name']} Bangladesh"]
                ]);
            } else {
                $megaCategory->update(['slug' => $megaData['slug']]);
            }

            foreach ($megaData['subCategories'] as $subData) {
                $miniBrandNames = array_map(function($m) { return $m['name']; }, $subData['miniCategories']);
                $subCategory = SubCategory::where('name', $subData['name'])->where('mega_category_id', $megaCategory->id)->where('company_id', $companyId)->first();
                if (!$subCategory) {
                    $subCategory = SubCategory::create([
                        'mega_category_id' => $megaCategory->id,
                        'name' => $subData['name'],
                        'company_id' => $companyId,
                        'slug' => $subData['slug'],
                        'status' => 1,
                        'description' => $this->generateSeoDescription($subData['name'], array_slice($miniBrandNames, 0, 10), $megaData['name']),
                        'meta_title' => "{$subData['name']} Price in Bangladesh",
                        'meta_description' => "Shop {$subData['name']} products in Bangladesh at Littlebaby, including quality {$subData['name']} products for babies and families. Explore authentic {$subData['name']} products at competitive prices with convenient online shopping and delivery.",
                        'meta_keywords' => ["{$subData['name']}", "Buy {$subData['name']}", "{$subData['name']} Price in BD", "{$subData['name']} Bangladesh"]
                    ]);
                } else {
                    $subCategory->update(['slug' => $subData['slug']]);
                }

                foreach ($subData['miniCategories'] as $miniData) {
                    $combinedName = "{$miniData['name']} {$subData['name']}";
                    $miniCategory = MiniCategory::where('name', $miniData['name'])->where('sub_category_id', $subCategory->id)->where('mega_category_id', $megaCategory->id)->where('company_id', $companyId)->first();
                    if (!$miniCategory) {
                        MiniCategory::create([
                            'mega_category_id' => $megaCategory->id,
                            'sub_category_id' => $subCategory->id,
                            'name' => $miniData['name'],
                            'company_id' => $companyId,
                            'slug' => $miniData['slug'],
                            'status' => 1,
                            'description' => $this->generateSeoDescription($combinedName, [], $megaData['name']),
                            'meta_title' => "{$combinedName} Price in Bangladesh",
                            'meta_description' => "Shop {$miniData['name']} products in Bangladesh at Littlebaby, including quality {$miniData['name']} products for babies and families. Explore authentic {$miniData['name']} products at competitive prices with convenient online shopping and delivery.",
                            'meta_keywords' => ["{$miniData['name']}", "Buy {$miniData['name']}", "{$miniData['name']} Price in BD", "{$miniData['name']} Bangladesh"]
                        ]);
                    } else {
                        $miniCategory->update(['slug' => $miniData['slug']]);
                    }
                }
            }
        }
    }
}
