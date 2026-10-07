<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\ContentSetting;
use App\Models\ExtraCategory;
use App\Models\MegaCategory;
use App\Models\MiniCategory;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductReview;
use App\Models\ProductVariation;
use App\Models\ProductView;
use App\Models\SearchProduct;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ProductController extends FrontendController
{

    public function getVariationModal($id)
    {
        $product = Product::with(['variations.attributes.attributeValue', 'variations.attributes.attributeGroup'])->findOrFail($id);

        $groupCategories = [];
        foreach ($product->variations as $variation) {
            foreach ($variation->attributes as $attr) {
                if ($attr->attributeGroup) {
                    $groupCategories[$attr->attributeGroup->name] = $attr->attributeGroup->category;
                }
            }
        }
        return $this->view('partials/variation_modal_content', compact('product', 'groupCategories'));
    }

    public function index(Request $request)
    {
        $breadcrumb = [['name' => 'All Products', 'slug' => 'shop']];
        $query = Product::with('variations:id,product_id,regular_price,discount,discount_type')
            ->select($this->getOptimizedProductSelects())
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', Status::Active->value);


        if ($request->filled('search')) {
            SearchProduct::create([
                'keyword'     => trim($request->search),
                'company_id'     => $this->company_id,
                'customer_id' => auth('customer')->id(),
                'status'      => Status::Active->value,
            ]);
        }
        $category = null;
        // Group Product
        if ($request->filled('group')) {
            $group = ProductGroup::where('slug', $request->group)->first();
            if ($group) {
                $category = $group;
                $request->merge(['filter_product_ids' => $group->product_ids]);
            }
        }

        $maxPriceLimit = $this->getMaxPriceLimit();
        $this->applyFiltersAndSorting($query, $request);
        if ($request->boolean('offers')) {

            $query->reorder();
            $query->selectRaw("products.*, (
                SELECT MAX(
                    CASE WHEN pv.discount_type = 'percent'
                        THEN (pv.regular_price * pv.discount / 100)
                        ELSE pv.discount
                    END
                )
                FROM product_variations pv
                WHERE pv.product_id = products.id
                  AND pv.discount > 0
            ) as variation_max_discount_amount")
                ->selectRaw("
                CASE WHEN products.discount_type = 'percent'
                    THEN (products.regular_price * products.discount / 100)
                    ELSE products.discount
                END as product_discount_amount
            ")
                ->where(function ($q) {
                    $q->where('products.discount', '>', 0)
                        ->orWhereHas('variations', function ($vq) {
                            $vq->where('discount', '>', 0);
                        });
                })
                ->orderByRaw('GREATEST(COALESCE(product_discount_amount, 0), COALESCE(variation_max_discount_amount, 0)) DESC')
                ->orderByDesc('products.created_at');
        }




        $products = $query->paginate(15);
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $template = $this->template ?? 'template1';
        
        $requirements = [
            'template1' => ['brands', 'attributeGroups'],
            'template2' => ['brands', 'attributeGroups'],
            'template3' => ['brands', 'attributeGroups'],
            'template4' => ['brands', 'attributeGroups'],
            'template5' => ['categories', 'attributeGroups'],
        ];

        $reqs = $requirements[$template] ?? $requirements['template1'];

        $brands = collect();
        if (in_array('brands', $reqs)) {
            $brands = Brand::select('id', 'name', 'slug')->get();
        }

        $categories = collect();
        if (in_array('categories', $reqs)) {
            $categories = MegaCategory::where('status', Status::Active->value)
                ->select('id', 'name', 'slug')
                ->with('subCategories:id,mega_category_id,name,slug')
                ->get();
        }

        $attributeGroups = collect();
        if (in_array('attributeGroups', $reqs)) {
            $attributeGroups = AttributeGroup::whereIn('name', ['Size', 'Color', 'Style'])
                ->with('values')
                ->where('status', Status::Active->value)
                ->get()
                ->unique('name');
        }

        return $this->view('frontend.shop', compact('products', 'brands', 'categories', 'attributeGroups', 'maxPriceLimit', 'breadcrumb'))->with([
            'category'    => null,
            'allProducts' => $products
        ]);
    }

    public function filterMenu(Request $request)
    {
        $query = Product::where('status', Status::Active->value)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($request->filled('category') && $request->category !== 'all') {
            $slug = $request->category;
            $column = null;
            $category = null;

            $category = MegaCategory::where('slug', $slug)->first();
            if ($category) {
                $column = 'mega_category_ids';
            }

            if (!$category) {
                $category = SubCategory::where('slug', $slug)->first();
                if ($category) {
                    $column = 'sub_category_ids';
                }
            }

            if (!$category) {
                $category = MiniCategory::where('slug', $slug)->first();
                if ($category) {
                    $column = 'mini_category_ids';
                }
            }

            if (!$category) {
                $category = ExtraCategory::where('slug', $slug)->first();
                if ($category) {
                    $column = 'extra_category_ids';
                }
            }

            if ($category && $column) {
                $query->whereJsonContains($column, (int) $category->id);
            }
        }

        // সার্চ ফিল্টার
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . trim($request->search) . '%');
        }

        $products = $query->latest()->take(12)->get();
        $products = Product::loadCategoriesForCollection($products);


        return $this->view('partials.menu-grid', compact('products'));
    }


    public function categoryProducts(Request $request, $slug)
    {
        $breadcrumb = [];
        $category = MegaCategory::where('slug', $slug)->first();
        $column = 'mega_category_ids';

        if ($category) {
            $column = 'mega_category_ids';
            $breadcrumb[] = ['name' => $category->name, 'slug' => $category->slug];
        }

        if (!$category) {
            $category = SubCategory::with('megaCategory')->where('slug', $slug)->first();
            if ($category) {
                $column = 'sub_category_ids';
                if ($category->megaCategory) {
                    $breadcrumb[] = ['name' => $category->megaCategory->name, 'slug' => $category->megaCategory->slug];
                }
                $breadcrumb[] = ['name' => $category->name, 'slug' => $category->slug];
            }
        }

        if (!$category) {
            $category = MiniCategory::with('subCategory.megaCategory')->where('slug', $slug)->first();
            if ($category) {
                $column = 'mini_category_ids';
                $sub = $category->subCategory;
                $mega = $sub?->megaCategory;
                if ($mega) $breadcrumb[] = ['name' => $mega->name, 'slug' => $mega->slug];
                if ($sub) $breadcrumb[] = ['name' => $sub->name, 'slug' => $sub->slug];
                $breadcrumb[] = ['name' => $category->name, 'slug' => $category->slug];
            }
        }

        if (!$category) {
            $category = ExtraCategory::with('miniCategory.subCategory.megaCategory')->where('slug', $slug)->first();
            if ($category) {
                $column = 'extra_category_ids';
                $mini = $category->miniCategory;
                $sub = $mini?->subCategory;
                $mega = $sub?->megaCategory;
                if ($mega) $breadcrumb[] = ['name' => $mega->name, 'slug' => $mega->slug];
                if ($sub) $breadcrumb[] = ['name' => $sub->name, 'slug' => $sub->slug];
                if ($mini) $breadcrumb[] = ['name' => $mini->name, 'slug' => $mini->slug];
                $breadcrumb[] = ['name' => $category->name, 'slug' => $category->slug];
            }
        }

        if (!$category) abort(401);

        $query = Product::with('variations:id,product_id,regular_price,discount,discount_type')
            ->select($this->getOptimizedProductSelects())
            ->where('status', Status::Active->value)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');


        if (!$request->filled('mega_category') && !$request->filled('sub_category')) {
            $query->whereJsonContains($column, (int) $category->id);
        }

        $this->applyFiltersAndSorting($query, $request);
        $maxPriceLimit = $this->getMaxPriceLimit();

        $products = $query->paginate(12)->appends($request->query());
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $template = $this->template ?? 'template1';
        
        $requirements = [
            'template1' => ['brands', 'attributeGroups'],
            'template2' => ['brands', 'attributeGroups'],
            'template3' => ['brands', 'attributeGroups'],
            'template4' => ['brands', 'attributeGroups'],
            'template5' => ['categories', 'attributeGroups'],
        ];

        $reqs = $requirements[$template] ?? $requirements['template1'];

        $brands = collect();
        if (in_array('brands', $reqs)) {
            $brands = Brand::select('id', 'name', 'slug')->get();
        }

        $categories = collect();
        if (in_array('categories', $reqs)) {
            $categories = MegaCategory::where('status', Status::Active->value)
                ->select('id', 'name', 'slug')
                ->with('subCategories:id,mega_category_id,name,slug')
                ->get();
        }

        $attributeGroups = collect();
        if (in_array('attributeGroups', $reqs)) {
            $attributeGroups = AttributeGroup::whereIn('name', ['Size', 'Color', 'Style'])
                ->with('values')
                ->where('status', Status::Active->value)
                ->get()
                ->unique('name');
        }

        return $this->view('frontend.shop', compact('products', 'brands', 'categories', 'attributeGroups', 'category', 'maxPriceLimit', 'breadcrumb'))->with([
            'allProducts' => $products
        ]);
    }

    // highest price limit
    private function getMaxPriceLimit()
    {
        $maxProductPrice = Product::max('regular_price');
        $maxVarPrice = ProductVariation::max('regular_price');

        return max((float)$maxProductPrice, (float)$maxVarPrice) ?: 1000;
    }

    // Variation Price Support
    private function applyFiltersAndSorting($query, $request)
    {
        // ১. প্রোডাক্ট আইডি দিয়ে ফিল্টার (গ্রুপ প্রোডাক্টের জন্য)
        if ($request->filled('filter_product_ids')) {
            $query->whereIn('id', (array)$request->filter_product_ids);
        }

        // ২. প্রাইস ফিল্টার (মেইন প্রাইস এবং ভ্যারিয়েশন প্রাইস উভয়ই চেক করবে)
        if ($request->filled('min_price')) {
            $min = $request->min_price;
            $query->where(function ($q) use ($min) {
                $q->where('regular_price', '>=', $min)
                    ->orWhereHas('variations', function ($v) use ($min) {
                        $v->where('regular_price', '>=', $min);
                    });
            });
        }

        if ($request->filled('max_price')) {
            $max = $request->max_price;
            $query->where(function ($q) use ($max) {
                $q->where(function ($sq) use ($max) {
                    $sq->where('regular_price', '<=', $max)->where('regular_price', '>', 0);
                })->orWhereHas('variations', function ($v) use ($max) {
                    $v->where('regular_price', '<=', $max);
                });
            });
        }

        // ৩. ব্র্যান্ড ফিল্টার
        if ($request->filled('brand')) {
            $query->whereIn('brand_id', (array)$request->brand);
        }
        // applyFiltersAndSorting() মেথডের ভিতরে যোগ করুন

        if ($request->filled('mega_category') || $request->filled('sub_category')) {
            $megaIds = (array) $request->mega_category;
            $subIds  = (array) $request->sub_category;

            $query->where(function ($q) use ($megaIds, $subIds) {
                foreach ($megaIds as $id) {
                    $q->orWhereJsonContains('mega_category_ids', (int) $id);
                }
                foreach ($subIds as $id) {
                    $q->orWhereJsonContains('sub_category_ids', (int) $id);
                }
            });
        }

        // ৪. অ্যাট্রিবিউট ফিল্টার (Color, Size ইত্যাদি)
        if ($request->filled('attributes')) {
            foreach ($request->attributes as $groupId => $ids) {
                $ids = array_filter((array)$ids);
                if (!empty($ids)) {
                    $query->whereHas('variations.attributes', function ($q) use ($ids) {
                        $q->whereIn('attribute_value_id', $ids);
                    });
                }
            }
        }

        // ৫. সার্চ ফিল্টার
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('sku_code', 'like', "%{$search}%");
            });
        }

        // ৬. সর্টিং
        if ($request->sort == 'price_low') {
            $query->orderBy('regular_price', 'asc');
        } elseif ($request->sort == 'price_high') {
            $query->orderBy('regular_price', 'desc');
        } else {
            $query->latest();
        }
    }

    public function productDetails($slug)
    {

        $product = Product::with([
            'variations.attributes.attributeGroup',
            'variations.attributes.attributeValue',
            'variations.galleries',
            'galleries',
            'brand:id,company_id,name,slug,logo',
            'reviews.customer',
            'reviews.variation.attributes.attributeGroup',
        ])->where('slug', $slug)->firstOrFail();

        $viewKey = 'viewed_product_' . $product->id;

        if (!session()->has($viewKey)) {
            ProductView::create([
                'company_id'  => getCurrentCompany()->company_id,
                'product_id'  => $product->id,
                'customer_id' => auth('customer')->id(),
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
            ]);

            session()->put($viewKey, true);
        }

        Product::loadCategoriesForCollection(collect([$product]));

        $attributeGroups = [];
        $formattedVariations = [];
        $valueImages = [];
        $groupCategories = [];

        if ($product->type === 'variation') {
            foreach ($product->variations as $variation) {
                $attrs = [];
                foreach ($variation->attributes as $attr) {
                    $groupName = $attr->attributeGroup?->name ?? 'Unknown';

                    if ($attr->attributeGroup && !isset($groupCategories[$groupName])) {
                        $groupCategories[$groupName] = $attr->attributeGroup->category;
                    }
                    $valId = $attr->attributeValue?->id;

                    if (!in_array($groupName, $attributeGroups)) $attributeGroups[] = $groupName;

                    $attrs[$groupName] = [
                        'id' => $valId,
                        'name' => $attr->attributeValue?->name ?? 'Unknown'
                    ];

                    if ($variation->image && $valId && !isset($valueImages[$valId])) {
                        $valueImages[$valId] = $variation->image_url;
                    }
                }

                $varGalleries = $variation->galleries->map(fn($g) => $g->image_url)->toArray();
                if ($variation->image) array_unshift($varGalleries, $variation->image_url);

                $formattedVariations[] = [
                    'id' => $variation->id,
                    'price' => $variation->final_price,
                    'regular_price' => $variation->regular_price,
                    'attributes' => $attrs,
                    'main_image' => $variation->image_url ?? $product->thumbnail_url,
                    'galleries' => $varGalleries,
                    'stock' => $variation->available_stock,
                    'sku' => $variation->sku
                ];
            }
        }

        $defaultGalleries = $product->galleries->map(fn($g) => $g->image_url)->toArray();
        array_unshift($defaultGalleries, $product->thumbnail_url);
        // Filter related products by product's category
        $subCategoryIds = (array) ($product->sub_category_ids ?? []);
        $megaCategoryIds = (array) ($product->mega_category_ids ?? []);

        $relatedProducts = collect();

        if (!empty($subCategoryIds) || !empty($megaCategoryIds)) {
            $relatedProducts = \Illuminate\Support\Facades\Cache::remember(
                "related_products_v2_{$product->id}",
                now()->addHours(6),
                function () use ($product, $subCategoryIds, $megaCategoryIds) {
                    $relatedQuery = Product::where('status', Status::Active->value)
                        ->where('id', '!=', $product->id);

                    $relatedQuery->where(function ($q) use ($subCategoryIds, $megaCategoryIds) {
                        if (!empty($subCategoryIds)) {
                            $q->where(function ($subQ) use ($subCategoryIds) {
                                foreach ($subCategoryIds as $id) {
                                    $subQ->orWhereJsonContains('sub_category_ids', (string) $id);
                                    $subQ->orWhereJsonContains('sub_category_ids', (int) $id);
                                }
                            });
                        }

                        if (!empty($megaCategoryIds)) {
                            $q->orWhere(function ($megaQ) use ($megaCategoryIds) {
                                foreach ($megaCategoryIds as $id) {
                                    $megaQ->orWhereJsonContains('mega_category_ids', (string) $id);
                                    $megaQ->orWhereJsonContains('mega_category_ids', (int) $id);
                                }
                            });
                        }
                    });

                    return $relatedQuery->with(['brand:id,company_id,name,slug,logo', 'variations'])->latest()->take(8)->get();
                }
            );
        }

        $trustBadges = \Illuminate\Support\Facades\Cache::remember('trust_badges_product', now()->addHours(24), function () {
            return ContentSetting::where('status', Status::Active->value)
                ->whereIn('page_type', [
                    ContentSetting::PAGE_PRODUCT,
                    ContentSetting::PAGE_ALL,
                    ContentSetting::PAGE_PRODUCT_SUB,
                ])
                ->ordered()
                ->get();
        });


        $allProductImages = [];
        $allProductImages[] = $product->thumbnail_url;

        // মেইন প্রোডাক্টের গ্যালারি ইমেজ
        foreach ($product->galleries as $g) {
            $allProductImages[] = $g->image_url;
        }

        // সব ভ্যারিয়েশনের ইমেজ এবং তাদের গ্যালারি ইমেজ
        if ($product->type === 'variation') {
            foreach ($product->variations as $variation) {
                if ($variation->image) {
                    $allProductImages[] = $variation->image_url;
                }
                foreach ($variation->galleries as $vGallery) {
                    $allProductImages[] = $vGallery->image_url;
                }
            }
        }

        // ডুপ্লিকেট ইমেজ বাদ দেওয়া এবং ইনডেক্স ঠিক করা
        $allProductImages = array_values(array_unique(array_filter($allProductImages)));
        $breadcrumb = [];
        // ক্যাটাগরিগুলো সিরিয়াল অনুযায়ী বের করা
        $mega = $product->mega_categories?->first();
        $sub = $product->sub_categories?->first();
        $mini = $product->mini_categories?->first();
        $extra = $product->extra_categories?->first();

        if ($mega) $breadcrumb[] = ['name' => $mega->name, 'slug' => $mega->slug];
        if ($sub) $breadcrumb[] = ['name' => $sub->name, 'slug' => $sub->slug];
        if ($mini) $breadcrumb[] = ['name' => $mini->name, 'slug' => $mini->slug];
        if ($extra) $breadcrumb[] = ['name' => $extra->name, 'slug' => $extra->slug];
        return $this->view('frontend.productDetails', compact(
            'product',
            'relatedProducts',
            'attributeGroups',
            'formattedVariations',
            'trustBadges',
            'valueImages',
            'defaultGalleries',
            'allProductImages',
            'groupCategories',
            'breadcrumb'
        ));
    }
    public function flashSale(Request $request)
    { // Filter products that have a discount > 0
        $query = Product::with('variations:id,product_id,regular_price,discount,discount_type')
            ->select($this->getOptimizedProductSelects())
            ->where('discount', '>', 0)
            ->where('status', Status::Active->value)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        $maxPriceLimit = $this->getMaxPriceLimit();

        $this->applyFiltersAndSorting($query, $request);

        $products = $query->paginate(12);
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $template = $this->template ?? 'template1';
        
        $requirements = [
            'template1' => ['brands', 'attributeGroups'],
            'template2' => ['brands', 'attributeGroups'],
            'template3' => ['brands', 'attributeGroups'],
            'template4' => ['brands', 'attributeGroups'],
            'template5' => ['categories', 'attributeGroups'],
        ];

        $reqs = $requirements[$template] ?? $requirements['template1'];

        $brands = collect();
        if (in_array('brands', $reqs)) {
            $brands = Brand::select('id', 'name', 'slug')->get();
        }

        $categories = collect();
        if (in_array('categories', $reqs)) {
            $categories = MegaCategory::where('status', Status::Active->value)
                ->select('id', 'name', 'slug')
                ->with('subCategories:id,mega_category_id,name,slug')
                ->get();
        }

        $attributeGroups = collect();
        if (in_array('attributeGroups', $reqs)) {
            $attributeGroups = AttributeGroup::whereIn('name', ['Size', 'Color', 'Style'])
                ->with('values')
                ->where('status', Status::Active->value)
                ->get()
                ->unique('name');
        }

        // Pass a virtual category object for the title
        $category = (object) ['name' => 'Flash Sale Items'];

        return $this->view('frontend.shop', compact('products', 'brands', 'attributeGroups', 'category', 'maxPriceLimit'));
    }


    public function brandProducts(Request $request, $slug)
    {
        $brand = Brand::where('slug', $slug)->firstOrFail();

        $query = Product::with('variations:id,product_id,regular_price,discount,discount_type')
            ->select($this->getOptimizedProductSelects())
            ->where('status', Status::Active->value)
            ->where('brand_id', $brand->id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');
        $maxPriceLimit = $this->getMaxPriceLimit();

        $this->applyFiltersAndSorting($query, $request);

        $products = $query->paginate(12);
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $template = $this->template ?? 'template1';
        
        $requirements = [
            'template1' => ['brands', 'attributeGroups'],
            'template2' => ['brands', 'attributeGroups'],
            'template3' => ['brands', 'attributeGroups'],
            'template4' => ['brands', 'attributeGroups'],
            'template5' => ['categories', 'attributeGroups'],
        ];

        $reqs = $requirements[$template] ?? $requirements['template1'];

        $brands = collect();
        if (in_array('brands', $reqs)) {
            $brands = Brand::select('id', 'name', 'slug')->get();
        }

        $categories = collect();
        if (in_array('categories', $reqs)) {
            $categories = MegaCategory::where('status', Status::Active->value)
                ->select('id', 'name', 'slug')
                ->with('subCategories:id,mega_category_id,name,slug')
                ->get();
        }

        $attributeGroups = collect();
        if (in_array('attributeGroups', $reqs)) {
            $attributeGroups = AttributeGroup::whereIn('name', ['Size', 'Color', 'Style'])
                ->with('values')
                ->where('status', Status::Active->value)
                ->get()
                ->unique('name');
        }

        $category = $brand;

        return $this->view('frontend.shop', compact('products', 'brands', 'categories', 'attributeGroups', 'category', 'maxPriceLimit'))->with([
            'allProducts' => $products
        ]);
    }
    public function searchSuggestions(Request $request)
    {
        $query = $request->get('q');
        $categorySlug = $request->get('category');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $productQuery = Product::where('status', Status::Active->value)
            ->where('title', 'LIKE', "%{$query}%");

        if (!empty($categorySlug)) {
            $category = MegaCategory::where('slug', $categorySlug)->first();

            if ($category) {
                $productQuery->whereJsonContains('mega_category_ids', (int)$category->id);
            }
        }

        $products = $productQuery->select('id', 'title', 'slug', 'thumbnail_95')
            ->take(10)
            ->get();

        $results = $products->map(function ($product) {
            return [
                'title' => $product->title,
                'slug'  => $product->slug,
                'thumbnail_95_url' => $product->thumbnail_95_url
            ];
        });

        return response()->json($results);
    }
    public function subcategoryProducts(Request $request, $mega_slug, $sub_slug)
    {
        $category = SubCategory::where('slug', $sub_slug)->firstOrFail();

        $query = Product::with('variations:id,product_id,regular_price,discount,discount_type')
            ->select($this->getOptimizedProductSelects())
            ->where('status', Status::Active->value)
            ->whereJsonContains('sub_category_ids', (int)$category->id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        return $this->renderShopView($request, $query, $category);
    }

    public function minicategoryProducts(Request $request,  $mega_slug, $sub_slug, $mini_slug)
    {
        $category = MiniCategory::where('slug', $mini_slug)->firstOrFail();

        $query = Product::with('variations:id,product_id,regular_price,discount,discount_type')
            ->select($this->getOptimizedProductSelects())
            ->where('status', Status::Active->value)
            ->whereJsonContains('mini_category_ids', (int)$category->id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        return $this->renderShopView($request, $query, $category);
    }

    private function renderShopView($request, $query, $category)
    {
        $this->applyFiltersAndSorting($query, $request);
        $maxPriceLimit = $this->getMaxPriceLimit();

        $products = $query->paginate(12)->appends($request->query());
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $template = $this->template ?? 'template1';
        
        $requirements = [
            'template1' => ['brands', 'attributeGroups'],
            'template2' => ['brands', 'attributeGroups'],
            'template3' => ['brands', 'attributeGroups'],
            'template4' => ['brands', 'attributeGroups'],
            'template5' => ['categories', 'attributeGroups'],
        ];

        $reqs = $requirements[$template] ?? $requirements['template1'];

        $brands = collect();
        if (in_array('brands', $reqs)) {
            $brands = Brand::select('id', 'name', 'slug')->get();
        }

        $categories = collect();
        if (in_array('categories', $reqs)) {
            $categories = MegaCategory::where('status', Status::Active->value)
                ->select('id', 'name', 'slug')
                ->with('subCategories:id,mega_category_id,name,slug')
                ->get();
        }

        $attributeGroups = collect();
        if (in_array('attributeGroups', $reqs)) {
            $attributeGroups = AttributeGroup::whereIn('name', ['Size', 'Color', 'Style'])
                ->with('values')
                ->where('status', Status::Active->value)
                ->get()
                ->unique('name');
        }

        return $this->view('frontend.shop', compact('products', 'brands', 'categories', 'attributeGroups', 'category', 'maxPriceLimit'))->with([
            'allProducts' => $products
        ]);
    }

    private function getOptimizedProductSelects()
    {
        $selects = [
            'products.id',
            'products.company_id',
            'products.brand_id',
            'products.title',
            'products.slug',
            'products.regular_price',
            'products.discount',
            'products.discount_type',
            'products.type',
            'products.status',
            'products.created_at',
        ];

        $template = $this->template ?? 'template1';
        if ($template === 'template4') {
            $selects[] = 'products.thumbnail_310';
            $selects[] = 'products.thumbnail_95';
        } else {
            $selects[] = 'products.thumbnail_310';
        }

        return $selects;
    }
}
