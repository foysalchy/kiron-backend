<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\MegaCategory;
use App\Models\Product;
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

        return view($template . '.frontend.shop', compact('products', 'brands', 'attributeGroups'))->with([
            'category' => null,
            'allProducts' => $products
        ]);
    }

    public function categoryProducts(Request $request, $store, $slug)
    {
        $company = getCurrentCompany();
        if (!$company) abort(401);

        $template = $company->template_name;

        $category = MegaCategory::where('slug', $slug)->where('company_id', $company->id)->firstOrFail();

        $query = Product::where('company_id', $company->id)
                    ->active()
                    ->whereJsonContains('mega_category_ids', (int)$category->id);

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
        return view($template . '.frontend.shop', compact('products', 'brands', 'attributeGroups', 'category'))->with([
            'allProducts' => $products
        ]);
    }

    private function applyFiltersAndSorting($query, $request)
    {
        if ($request->filled('min_price')) {
            $query->where('regular_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('regular_price', '<=', $request->max_price);
        }

        if ($request->filled('brand')) {
            $query->whereIn('brand_id', (array)$request->brand);
        }

        if ($request->filled('attributes')) {
            foreach ($request->attributes as $groupId => $ids) {
                $ids = array_filter((array)$ids);
                if (!empty($ids)) {
                    $query->whereHas('variations.attributes', function($q) use ($ids) {
                        $q->whereIn('attribute_value_id', $ids);
                    });
                }
            }
        }

        if ($request->sort == 'price_low') {
            $query->orderBy('regular_price', 'asc');
        }
        elseif ($request->sort == 'price_high') {
            $query->orderBy('regular_price', 'desc');
        }
        elseif ($request->sort == 'newest') {
            $query->latest();
        }
        else {
            $query->latest();
        }
    }

    public function productDetails($store, $slug)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        // ১. variations.galleries সহ সব রিলেশন লোড করা
        $product = Product::with([
            'variations.attributes.attributeGroup',
            'variations.attributes.attributeValue',
            'variations.galleries', // ভ্যারিয়েশন গ্যালারি লোড
            'galleries',
            'brand'
        ])->where('slug', $slug)->where('company_id', $company->id)->firstOrFail();



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

                // ২. ভ্যারিয়েশনের নিজস্ব গ্যালারি ইমেজগুলো নেওয়া
                $varGalleries = $variation->galleries->map(function($g) {
                    return asset('storage/' . $g->image);
                })->toArray();

                $formattedVariations[] = [
                    'id' => $variation->id,
                    'price' => $variation->final_price,
                    'attributes' => $attrs,
                    'main_image' => $variation->image ? asset('storage/'.$variation->image) : $product->thumbnail_url,
                    'galleries' => $varGalleries // গ্যালারি ডাটা পাঠানো হচ্ছে
                ];
            }
        }
//  \Log::info($formattedVariations);
        $relatedProducts = Product::where('company_id', $company->id)->active()
                            ->where('id', '!=', $product->id)->latest()->take(8)->get();

        return view($template . '.frontend.productDetails', compact('product', 'relatedProducts', 'attributeGroups', 'formattedVariations'));
    }
}
