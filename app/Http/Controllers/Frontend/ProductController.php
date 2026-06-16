<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\ContentSetting;
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
    public function getVariationModal($store, $id)
    {
        $product = Product::with(['variations.attributes.attributeValue', 'variations.attributes.attributeGroup'])
            ->findOrFail($id);

        return $this->view('partials/variation_modal_content', compact('product'));
    }
    public function index(Request $request, $store)
    {
        $query = Product::with('variations')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');;

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

        $products = $query->paginate(15);
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $brands          = Brand::get();
        $attributeGroups = AttributeGroup::whereIn('name', ['Size', 'Color', 'Style'])
            ->with('values')
            ->where('status', Status::Active->value)
            ->get()
            ->unique('name');

        return $this->view('frontend.shop', compact('products', 'brands', 'attributeGroups', 'maxPriceLimit'))->with([
            'category'    => null,
            'allProducts' => $products
        ]);
    }

    public function categoryProducts(Request $request, $store, $slug)
    {
        $category = MegaCategory::where('slug', $slug)->first();
        $column = 'mega_category_ids';


        if (!$category) {
            $category = SubCategory::where('slug', $slug)->first();
            $column = 'sub_category_ids';
        }
        if (!$category) {
            $category = MiniCategory::where('slug', $slug)->first();
            $column = 'mini_category_ids';
        }

        if (!$category) abort(401);

        $query = Product::where('status', Status::Active->value)
            ->whereJsonContains('mega_category_ids', (int)$category->id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        $this->applyFiltersAndSorting($query, $request);
        $maxPriceLimit = $this->getMaxPriceLimit();

        $products = $query->paginate(12);
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $brands = Brand::get();

        $attributeGroups = AttributeGroup::whereIn('name', ['Size', 'Color', 'Style'])
            ->with('values')
            ->where('status', Status::Active->value)
            ->get()
            ->unique('name');
        return $this->view('frontend.shop', compact('products', 'brands', 'attributeGroups', 'category', 'maxPriceLimit'))->with([
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
        // group product
        if ($request->filled('filter_product_ids')) {
            $ids = (array)$request->filter_product_ids;
            $query->whereIn('id', $ids);
        }
        if ($request->filled('min_price')) {
            $min = $request->min_price;
            $query->where(function ($q) use ($min) {
                $q->where('regular_price', '>=', $min)
                    ->orWhereHas('variations', function ($sq) use ($min) {
                        $sq->where('regular_price', '>=', $min);
                    });
            });
        }

        if ($request->filled('max_price')) {
            $max = $request->max_price;
            $query->where(function ($q) use ($max) {
                $q->where(function ($sub) use ($max) {
                    $sub->where('regular_price', '<=', $max)->where('regular_price', '>', 0);
                })
                    ->orWhereHas('variations', function ($sq) use ($max) {
                        $sq->where('regular_price', '<=', $max);
                    });
            });
        }

        if ($request->filled('brand') && !request()->routeIs('brand.products')) {
            $query->whereIn('brand_id', (array)$request->brand);
        }

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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('sku_code', 'like', "%{$search}%");
            });
        }

        //
        if ($request->sort == 'price_low') {
            $query->orderBy('regular_price', 'asc');
        } elseif ($request->sort == 'price_high') {
            $query->orderBy('regular_price', 'desc');
        } else {
            $query->latest();
        }
    }

    public function productDetails($store, $slug)
    {
        $product = Product::with([
            'variations.attributes.attributeGroup',
            'variations.attributes.attributeValue',
            'variations.galleries',
            'galleries',
            'brand',
            'reviews.customer',
            'reviews.variation.attributes.attributeGroup',
        ])->where('slug', $slug)->firstOrFail();

        $viewKey = 'viewed_product_' . $product->id;

        if (!session()->has($viewKey)) {
            ProductView::create([
                'company_id'  => $this->company_id,
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

        if ($product->type === 'variation') {
            foreach ($product->variations as $variation) {
                $attrs = [];
                foreach ($variation->attributes as $attr) {
                    $groupName = $attr->attributeGroup?->name ?? 'Unknown';
                    if (!in_array($groupName, $attributeGroups)) $attributeGroups[] = $groupName;
                    $attrs[$groupName] = ['id' => $attr->attributeValue?->id, 'name' => $attr->attributeValue?->name ?? 'Unknown'];
                }

                $varGalleries = $variation->galleries->map(function ($g) {
                    return asset('storage/' . $g->image);
                })->toArray();

                $formattedVariations[] = [
                    'id' => $variation->id,
                    'price' => $variation->final_price,
                    'attributes' => $attrs,
                    'main_image' => $variation->image ? asset('storage/' . $variation->image) : $product->thumbnail_url,
                    'galleries' => $varGalleries,
                    'stock' => $variation->available_stock
                ];
            }
        }
        //  \Log::info($formattedVariations);
        $relatedProducts = Product::where('status', Status::Active->value)
            ->where('id', '!=', $product->id)->latest()->take(8)->get();

        $trustBadges = ContentSetting::where('status', Status::Active->value)
            ->whereIn('page_type', [
                ContentSetting::PAGE_PRODUCT,
                ContentSetting::PAGE_ALL,
                ContentSetting::PAGE_PRODUCT_SUB,
            ])
            ->ordered()
            ->get();

        return $this->view('frontend.productDetails', compact('product', 'relatedProducts', 'attributeGroups', 'formattedVariations', 'trustBadges'));
    }
    public function flashSale(Request $request, $store)
    { // Filter products that have a discount > 0
        $query = Product::where('discount', '>', 0)
            ->where('status', Status::Active->value)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');;

        $maxPriceLimit = $this->getMaxPriceLimit();

        $this->applyFiltersAndSorting($query, $request);

        $products = $query->paginate(12);
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $brands = Brand::get();

        $attributeGroups = AttributeGroup::whereIn('name', ['Size', 'Color', 'Style'])
            ->with('values')
            ->where('status', Status::Active->value)
            ->get();

        // Pass a virtual category object for the title
        $category = (object) ['name' => 'Flash Sale Items'];

        return $this->view('frontend.shop', compact('products', 'brands', 'attributeGroups', 'category', 'maxPriceLimit'));
    }


    public function brandProducts(Request $request, $store, $slug)
    {
        $brand = Brand::where('slug', $slug)->firstOrFail();

        $query = Product::with('variations')
            ->where('status', Status::Active->value)
            ->where('brand_id', $brand->id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');
        $maxPriceLimit = $this->getMaxPriceLimit();

        $this->applyFiltersAndSorting($query, $request);

        $products = $query->paginate(12);
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $brands = Brand::get();

        $attributeGroups = AttributeGroup::whereIn('name', ['Size', 'Color', 'Style'])
            ->with('values')
            ->get();

        $category = $brand;

        return $this->view('frontend.shop', compact('products', 'brands', 'attributeGroups', 'category', 'maxPriceLimit'))->with([
            'allProducts' => $products
        ]);
    }
    public function searchSuggestions(Request $request)
    {
        $query = $request->get('q');
        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::where('status', Status::Active->value)
            ->where('title', 'LIKE', "%{$query}%")
            ->select('id', 'title', 'slug', 'thumbnail')
            ->take(10)
            ->get();

        $results = $products->map(function ($product) {
            return [
                'title' => $product->title,
                'slug'  => $product->slug,
                'thumbnail_url' => $product->thumbnail_url
            ];
        });

        return response()->json($results);
    }
}
