<?php

namespace App\Services;

use App\Models\ContentSetting;
use App\Models\Product;
use Illuminate\Support\Collection;

class StorePreviewService
{
    /**
     * Check if the current request is in preview mode
     */
    public static function isPreview(): bool
    {
        return !empty(request('preview_theme'));
    }

    /**
     * Get dummy main hero sliders
     */
    public static function getDummySliders(): Collection
    {
        $defaultImg = asset('images/template1/frontend/dorja-sample.webp');

        return collect([
            (object) [
                'id' => 'dummy-slider-1',
                'title' => 'Mega Season Sale - Up to 50% Off',
                'subtitle' => 'Exclusive Collection Available Now',
                'image_url' => $defaultImg,
                'mobile_image_url' => $defaultImg,
                'url' => '#',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 'dummy-slider-2',
                'title' => 'New Premium Arrivals',
                'subtitle' => 'Trending styles of the season',
                'image_url' => $defaultImg,
                'mobile_image_url' => $defaultImg,
                'url' => '#',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 'dummy-slider-3',
                'title' => 'Limited Time Flash Offers',
                'subtitle' => 'Grab your favorite item today',
                'image_url' => $defaultImg,
                'mobile_image_url' => $defaultImg,
                'url' => '#',
                'is_dummy' => true,
            ],
        ]);
    }

    /**
     * Get dummy sidebar vertical sliders
     */
    public static function getDummySidebarSliders(): Collection
    {
        $defaultImg = asset('images/template1/frontend/dorja-sample.webp');

        return collect([
            (object) [
                'id' => 'dummy-side-1',
                'title' => 'Special Weekend Deal',
                'image_url' => $defaultImg,
                'url' => '#',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 'dummy-side-2',
                'title' => 'Hot Best Sellers',
                'image_url' => $defaultImg,
                'url' => '#',
                'is_dummy' => true,
            ],
        ]);
    }

    /**
     * Get dummy middle dual banner sliders
     */
    public static function getDummyMiddleSliders(): Collection
    {
        $defaultImg = asset('images/template1/frontend/dorja-sample.webp');

        return collect([
            (object) [
                'id' => 'dummy-mid-1',
                'title' => 'Exclusive Offer - Buy 1 Get 1',
                'image_url' => $defaultImg,
                'url' => '#',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 'dummy-mid-2',
                'title' => 'Free Nationwide Delivery',
                'image_url' => $defaultImg,
                'url' => '#',
                'is_dummy' => true,
            ],
        ]);
    }

    /**
     * Get dummy top categories
     */
    public static function getDummyCategories(): Collection
    {
        $defaultImg = asset('images/template1/frontend/dorja-sample.webp');

        $categories = [
            ['name' => 'Men\'s Fashion', 'slug' => 'mens-fashion', 'count' => 18],
            ['name' => 'Women\'s Collection', 'slug' => 'womens-collection', 'count' => 24],
            ['name' => 'Electronics & Gadgets', 'slug' => 'electronics', 'count' => 15],
            ['name' => 'Home & Lifestyle', 'slug' => 'home-lifestyle', 'count' => 12],
            ['name' => 'Health & Beauty', 'slug' => 'beauty', 'count' => 9],
            ['name' => 'Sports & Outdoor', 'slug' => 'sports', 'count' => 7],
        ];

        return collect($categories)->map(function ($cat, $index) use ($defaultImg) {
            return (object) [
                'id' => 'dummy-cat-' . ($index + 1),
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'image_url' => $defaultImg,
                'product_count' => $cat['count'],
                'is_dummy' => true,
            ];
        });
    }

    /**
     * Get dummy featured categories array structure for home page carousel
     */
    public static function getDummyFeatureCategory(): array
    {
        $defaultImg = asset('images/template1/frontend/dorja-sample.webp');

        return [
            'name' => 'Featured Categories',
            'is_dummy' => true,
            'items' => [
                ['label' => 'Men\'s Fashion', 'link' => '#', 'image' => $defaultImg, 'product_count' => 18, 'visible' => true],
                ['label' => 'Women\'s Collection', 'link' => '#', 'image' => $defaultImg, 'product_count' => 24, 'visible' => true],
                ['label' => 'Electronics & Gadgets', 'link' => '#', 'image' => $defaultImg, 'product_count' => 15, 'visible' => true],
                ['label' => 'Home & Lifestyle', 'link' => '#', 'image' => $defaultImg, 'product_count' => 12, 'visible' => true],
                ['label' => 'Health & Beauty', 'link' => '#', 'image' => $defaultImg, 'product_count' => 9, 'visible' => true],
                ['label' => 'Sports & Outdoor', 'link' => '#', 'image' => $defaultImg, 'product_count' => 7, 'visible' => true],
            ],
        ];
    }

    /**
     * Get dummy products compatible with templates and product card components
     */
    public static function getDummyProducts(int $count = 6): Collection
    {
        $defaultImg = asset('images/template1/frontend/dorja-sample.webp');

        $titles = [
            'Wireless Noise Cancelling Bluetooth Headphones',
            'Smart Sports Fitness Tracker Watch',
            'Premium Cotton Casual Long Sleeve Shirt',
            'Stainless Steel Thermal Vacuum Water Bottle',
            'Fast Wireless Fast Charging Pad Stand',
            'Ultra Slim Waterproof Travel Backpack',
            'Compact Portable Bluetooth Stereo Speaker',
            'Ergonomic Wireless Optical Mouse',
            'Classic Polarized UV Protection Sunglasses',
            'Quick Dry Breathable Running Sneakers',
            'Mechanical Gaming Keyboard RGB Backlit',
            'Dual USB Power Bank High Capacity 20000mAh',
        ];

        return collect(range(0, $count - 1))->map(function ($i) use ($titles, $defaultImg) {
            $title = $titles[$i % count($titles)];
            $regularPrice = 1200 + ($i * 150);
            $salePrice = 850 + ($i * 120);

            $product = new Product();
            $product->id = 90000 + $i;
            $product->company_id = 1;
            $product->title = $title . ' (Sample)';
            $product->slug = 'sample-product-' . ($i + 1);
            $product->regular_price = $regularPrice;
            $product->purchase_price = 600;
            $product->sale_price = $salePrice;
            $product->discount = $regularPrice - $salePrice;
            $product->discount_type = 'flat';
            $product->available_stock = 25;
            $product->stock_status = 'in_stock';
            $product->manage_stock = 1;
            $product->type = 'single';
            $product->status = 1;
            $product->short_description = 'This is a sample product preview for testing store layout and typography.';
            $product->is_free_delivery = ($i % 3 === 0) ? 1 : 0;
            $product->thumbnail_url = $defaultImg;
            $product->thumbnail_310_url = $defaultImg;
            $product->is_dummy = true;

            // Mock display_price_data for templates using it
            $product->display_price_data = (object) [
                'regular_price' => $regularPrice,
                'sale_price' => $salePrice,
            ];

            // Set relations for templates that call them
            $product->setRelation('brand', (object) ['name' => 'Premium Brand', 'slug' => '#']);
            $product->setRelation('mega_categories', collect([(object) ['name' => 'Featured', 'slug' => '#']]));
            $product->setRelation('sub_categories', collect([(object) ['name' => 'Popular', 'slug' => '#']]));
            $product->setRelation('galleries', collect([]));
            $product->setRelation('variations', collect([]));

            return $product;
        });
    }

    /**
     * Get dummy product groups with inner products
     */
    public static function getDummyProductGroups(): Collection
    {
        $productsGroup1 = self::getDummyProducts(6);
        $productsGroup2 = self::getDummyProducts(6);

        return collect([
            (object) [
                'id' => 'dummy-group-1',
                'name' => '🔥 Flash Deals & Hot Offers',
                'slug' => 'hot-deals',
                'products' => $productsGroup1,
                'is_dummy' => true,
            ],
            (object) [
                'id' => 'dummy-group-2',
                'name' => '⭐ Best Selling Collections',
                'slug' => 'best-selling',
                'products' => $productsGroup2,
                'is_dummy' => true,
            ],
        ]);
    }

    /**
     * Get dummy notice marquee items
     */
    public static function getDummyNotices(): Collection
    {
        return collect([
            (object) [
                'id' => 1,
                'title' => '🎉 Welcome to our official online store! Enjoy cash on delivery all across Bangladesh.',
                'slug' => 'sample-notice-1',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 2,
                'title' => '🚚 Free shipping on orders over ৳1500. Order now and receive in 24-48 hours.',
                'slug' => 'sample-notice-2',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 3,
                'title' => '⭐ 100% Genuine and authentic products with 7-day hassle-free replacement warranty.',
                'slug' => 'sample-notice-3',
                'is_dummy' => true,
            ],
        ]);
    }

    /**
     * Get dummy product page custom rich text block
     */
    public static function getDummyCustomBlock(): ContentSetting
    {
        $setting = new ContentSetting();
        $setting->id = 99999;
        $setting->page_type = ContentSetting::PAGE_PRODUCT;
        $setting->title = 'Authentic Quality & Warranty Assurance (Sample Preview)';
        $setting->status = 1;
        $setting->is_dummy = true;
        $setting->text_content = '
            <div style="font-size: 14px; line-height: 1.6;">
                <p style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">⭐ 100% Genuine & Authentic Products</p>
                <ul style="margin: 0; padding-left: 20px; color: #475569;">
                    <li><strong>Official Warranty:</strong> Enjoy up to 1-Year official service warranty coverage.</li>
                    <li><strong>Money-Back Guarantee:</strong> 100% full refund if the item is proven non-authentic.</li>
                    <li><strong>Fast Delivery:</strong> Same-day dispatch with live parcel tracking.</li>
                    <li><strong>Easy Return:</strong> 7-day hassle-free replacement guarantee.</li>
                </ul>
            </div>
        ';

        return $setting;
    }

    /**
     * Get dummy home page SEO description
     */
    public static function getDummyHomeDescription(): object
    {
        return (object) [
            'is_dummy' => true,
            'description' => '
                <h2>Welcome to Our Premium E-Commerce Store</h2>
                <p>We are dedicated to providing you with the best shopping experience, focusing on high quality, exceptional customer support, and lightning-fast nationwide delivery. Browse our vast collection of curated items across multiple categories.</p>
                <p>Each product in our catalog undergoes rigorous quality checks to ensure complete customer satisfaction. Shop with peace of mind using our secure payment gateways and convenient Cash on Delivery (COD) services.</p>
            ',
        ];
    }

    /**
     * Get dummy brand list
     */
    public static function getDummyBrands(): Collection
    {
        $defaultLogo = asset('images/template1/frontend/dorja-sample.webp');

        return collect([
            (object) ['id' => 1, 'name' => 'Brand Vanguard', 'slug' => '#', 'logo' => $defaultLogo, 'is_dummy' => true],
            (object) ['id' => 2, 'name' => 'Aura Tech', 'slug' => '#', 'logo' => $defaultLogo, 'is_dummy' => true],
            (object) ['id' => 3, 'name' => 'Zenith Life', 'slug' => '#', 'logo' => $defaultLogo, 'is_dummy' => true],
            (object) ['id' => 4, 'name' => 'Nexus Prime', 'slug' => '#', 'logo' => $defaultLogo, 'is_dummy' => true],
            (object) ['id' => 5, 'name' => 'Lumina Elite', 'slug' => '#', 'logo' => $defaultLogo, 'is_dummy' => true],
        ]);
    }

    /**
     * Get dummy FAQs for home/product pages
     */
    public static function getDummyFaqs(): Collection
    {
        return collect([
            (object) [
                'question' => 'How can I place an order?',
                'answer' => 'Simply browse your desired product, choose variations if available, and click "Order Now" or "Add to Cart" to proceed to our fast checkout page.',
                'is_dummy' => true,
            ],
            (object) [
                'question' => 'What are the delivery charges and delivery times?',
                'answer' => 'Delivery inside Dhaka takes 24 to 48 hours, and outside Dhaka takes 2 to 3 days. Standard delivery charge is ৳60 inside Dhaka and ৳120 outside Dhaka.',
                'is_dummy' => true,
            ],
            (object) [
                'question' => 'Can I check the parcel before making payment?',
                'answer' => 'Yes, you can inspect the package in front of our delivery executive before making the payment.',
                'is_dummy' => true,
            ],
        ]);
    }

    /**
     * Get dummy customer reviews
     */
    public static function getDummyReviews(): Collection
    {
        return collect([
            (object) [
                'id' => 1,
                'customer' => (object) [
                    'name' => 'Tanvir Ahmed',
                    'image' => null,
                ],
                'rating' => 5,
                'comment' => 'Excellent product quality and super fast delivery. Highly recommended store!',
                'created_at' => now()->subDays(2),
                'is_dummy' => true,
            ],
            (object) [
                'id' => 2,
                'customer' => (object) [
                    'name' => 'Farhana Kabir',
                    'image' => null,
                ],
                'rating' => 5,
                'comment' => 'Packaging was top-notch and the item matched exactly as shown on the website.',
                'created_at' => now()->subDays(5),
                'is_dummy' => true,
            ],
            (object) [
                'id' => 3,
                'customer' => (object) [
                    'name' => 'Sabbir Hossain',
                    'image' => null,
                ],
                'rating' => 4,
                'comment' => 'Very satisfied with customer service. Will definitely order again.',
                'created_at' => now()->subDays(8),
                'is_dummy' => true,
            ],
        ]);
    }

    public static function getDummyFooterFeatures()
    {
        return collect([
            (object) [
                'id' => 1,
                'title' => 'Fast Delivery',
                'subtitle' => 'Fast delivery within 24-48 hours',
                'text_content' => 'Fast delivery within 24-48 hours',
                'icon_file' => null,
                'icon_url' => 'fas fa-truck',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 2,
                'title' => 'Quality Guarantee',
                'subtitle' => '100% original and premium products',
                'text_content' => '100% original and premium products',
                'icon_file' => null,
                'icon_url' => 'fas fa-shield-alt',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 3,
                'title' => 'Secure Payment',
                'subtitle' => 'Cash on delivery & online payment',
                'text_content' => 'Cash on delivery & online payment',
                'icon_file' => null,
                'icon_url' => 'fas fa-credit-card',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 4,
                'title' => 'Customer Support',
                'subtitle' => '24/7 dedicated customer assistance',
                'text_content' => '24/7 dedicated customer assistance',
                'icon_file' => null,
                'icon_url' => 'fas fa-headset',
                'is_dummy' => true,
            ],
        ]);
    }

    public static function getDummyFooterPages()
    {
        return collect([
            (object) [
                'id' => 1,
                'title' => 'About Us',
                'slug' => 'about-us',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 2,
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 3,
                'title' => 'Terms & Conditions',
                'slug' => 'terms-and-conditions',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 4,
                'title' => 'Return & Refund Policy',
                'slug' => 'return-and-refund-policy',
                'is_dummy' => true,
            ],
        ]);
    }

    public static function getDummySocialLinks()
    {
        return collect([
            (object) [
                'id' => 1,
                'title' => 'facebook',
                'link' => 'https://facebook.com',
                'icon' => 'fa-brands fa-facebook-f',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 2,
                'title' => 'instagram',
                'link' => 'https://instagram.com',
                'icon' => 'fa-brands fa-instagram',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 3,
                'title' => 'youtube',
                'link' => 'https://youtube.com',
                'icon' => 'fa-brands fa-youtube',
                'is_dummy' => true,
            ],
            (object) [
                'id' => 4,
                'title' => 'tiktok',
                'link' => 'https://tiktok.com',
                'icon' => 'fa-brands fa-tiktok',
                'is_dummy' => true,
            ],
        ]);
    }

    public static function getDummyFooterBottomRight()
    {
        return collect([
            (object) [
                'id' => 1,
                'title' => 'bKash',
                'icon_file' => null,
                'icon_url' => null,
                'is_dummy' => true,
            ],
            (object) [
                'id' => 2,
                'title' => 'Nagad',
                'icon_file' => null,
                'icon_url' => null,
                'is_dummy' => true,
            ],
            (object) [
                'id' => 3,
                'title' => 'Visa',
                'icon_file' => null,
                'icon_url' => null,
                'is_dummy' => true,
            ],
            (object) [
                'id' => 4,
                'title' => 'Mastercard',
                'icon_file' => null,
                'icon_url' => null,
                'is_dummy' => true,
            ],
        ]);
    }
}
