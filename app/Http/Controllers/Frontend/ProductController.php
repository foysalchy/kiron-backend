<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\MegaCategory;
use App\Models\MiniCategory;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getVariationModal($store, $id)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        $product = Product::with(['variations.attributes.attributeValue', 'variations.attributes.attributeGroup'])
            ->findOrFail($id);

        return view($template . '.partials.variation_modal_content', compact('product'));
    }
    public function index(Request $request, $store)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        $query = Product::with('variations')->where('company_id', $company->id);

        // dd($query);
$maxPriceLimit = $this->getMaxPriceLimit($company->id);

        $this->applyFiltersAndSorting($query, $request);

        $products = $query->paginate(12);
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $brands = Brand::where('company_id', $company->id)->get();

        $attributeGroups = AttributeGroup::where('company_id', $company->id)
            ->whereIn('name', ['Size', 'Color', 'Style'])
            ->with('values')
            ->active()
            ->get()
            ->unique('name');

        return view($template . '.frontend.shop', compact('products', 'brands', 'attributeGroups','maxPriceLimit'))->with([
            'category' => null,
            'allProducts' => $products
        ]);
    }

    public function categoryProducts(Request $request, $store, $slug)
    {
        $company = getCurrentCompany();
        if (!$company) abort(401);

        $template = $company->template_name;

        $category = MegaCategory::where('slug', $slug)->where('company_id', $company->id)->first();
        $column = 'mega_category_ids';


        if (!$category) {
            $category = SubCategory::where('slug', $slug)->where('company_id', $company->id)->first();
            $column = 'sub_category_ids';
        }
        if (!$category) {
            $category = MiniCategory::where('slug', $slug)->where('company_id', $company->id)->first();
            $column = 'mini_category_ids';
        }

        if (!$category) abort(401);

        $query = Product::where('company_id', $company->id)
            ->active()
            ->whereJsonContains('mega_category_ids', (int)$category->id);

        $this->applyFiltersAndSorting($query, $request);
        $maxPriceLimit = $this->getMaxPriceLimit($company->id);

        $products = $query->paginate(12);
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $brands = Brand::where('company_id', $company->id)->get();

        $attributeGroups = AttributeGroup::where('company_id', $company->id)
            ->whereIn('name', ['Size', 'Color', 'Style'])
            ->with('values')
            ->active()
            ->get()
            ->unique('name');
        return view($template . '.frontend.shop', compact('products', 'brands', 'attributeGroups', 'category','maxPriceLimit'))->with([
            'allProducts' => $products
        ]);
    }

   // ১. ডাটাবেস থেকে সর্বোচ্চ দাম বের করার ডাইনামিক ফাংশন
private function getMaxPriceLimit($companyId)
{
    $maxProductPrice = Product::where('company_id', $companyId)->max('regular_price');
    $maxVarPrice = \App\Models\ProductVariation::whereHas('product', function($q) use ($companyId) {
        $q->where('company_id', $companyId);
    })->max('regular_price');

    return max((float)$maxProductPrice, (float)$maxVarPrice) ?: 1000;
}

// ২. ফিল্টার ফাংশন (Variation Price Support সহ আপডেট করা হয়েছে)
private function applyFiltersAndSorting($query, $request)
{
    if ($request->filled('min_price')) {
        $min = $request->min_price;
        $query->where(function($q) use ($min) {
            $q->where('regular_price', '>=', $min)
              ->orWhereHas('variations', function($sq) use ($min) {
                  $sq->where('regular_price', '>=', $min);
              });
        });
    }

    if ($request->filled('max_price')) {
        $max = $request->max_price;
        $query->where(function($q) use ($max) {
            $q->where(function($sub) use ($max) {
                $sub->where('regular_price', '<=', $max)->where('regular_price', '>', 0);
            })
            ->orWhereHas('variations', function($sq) use ($max) {
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

    // সর্টিং লজিক...
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
        $company = getCurrentCompany();
        $template = $company->template_name;

        $product = Product::with([
            'variations.attributes.attributeGroup',
            'variations.attributes.attributeValue',
            'variations.galleries',
            'galleries',
            'brand'
        ])->where('slug', $slug)->where('company_id', $company->id)->firstOrFail();


        $viewKey = 'viewed_product_' . $product->id;

        if (!session()->has($viewKey)) {
            ProductView::create([
                'company_id'  => $company->id,
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
                    'galleries' => $varGalleries
                ];
            }
        }
        //  \Log::info($formattedVariations);
        $relatedProducts = Product::where('company_id', $company->id)->active()
            ->where('id', '!=', $product->id)->latest()->take(8)->get();

        return view($template . '.frontend.productDetails', compact('product', 'relatedProducts', 'attributeGroups', 'formattedVariations'));
    }
    public function flashSale(Request $request, $store)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        // Filter products that have a discount > 0
        $query = Product::where('company_id', $company->id)
            ->where('discount', '>', 0)
            ->active();

        $maxPriceLimit = $this->getMaxPriceLimit($company->id);

        $this->applyFiltersAndSorting($query, $request);

        $products = $query->paginate(12);
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $brands = Brand::where('company_id', $company->id)->get();

        $attributeGroups = AttributeGroup::where('company_id', $company->id)
            ->whereIn('name', ['Size', 'Color', 'Style'])
            ->with('values')
            ->active()
            ->get();

        // Pass a virtual category object for the title
        $category = (object) ['name' => 'Flash Sale Items'];

        return view($template . '.frontend.shop', compact('products', 'brands', 'attributeGroups', 'category','maxPriceLimit'));
    }


    public function brandProducts(Request $request, $store, $slug)
    {
        $company = getCurrentCompany();
        if (!$company) abort(401);

        $template = $company->template_name;

        $brand = Brand::where('slug', $slug)->where('company_id', $company->id)->firstOrFail();

        $query = Product::with('variations')
            ->where('company_id', $company->id)
            ->where('status', Status::Active->value)
            ->where('brand_id', $brand->id);
        $maxPriceLimit = $this->getMaxPriceLimit($company->id);

        $this->applyFiltersAndSorting($query, $request);

        $products = $query->paginate(12);
        $products->setCollection(Product::loadCategoriesForCollection($products->getCollection()));

        $brands = Brand::where('company_id', $company->id)->get();

        $attributeGroups = AttributeGroup::where('company_id', $company->id)
            ->whereIn('name', ['Size', 'Color', 'Style'])
            ->with('values')
            ->get();

        $category = $brand;

        return view($template . '.frontend.shop', compact('products', 'brands', 'attributeGroups', 'category','maxPriceLimit'))->with([
            'allProducts' => $products
        ]);
    }
}
