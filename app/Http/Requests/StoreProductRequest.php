<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        if (!$this->has('slug') | empty($this->slug)) {
            $merge['slug'] = \Illuminate\Support\Str::slug($this->title) . '-' . time();
        }
        if (!$this->has('manage_stock') | $this->manage_stock === null) {
            $merge['manage_stock'] = 1;
        }
        if (is_array($this->sku_code)) {
            $merge['sku_code'] = $this->sku_code[0] ?? null;
        }
        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        $companyId = $this->input('company_id') ?? $this->user()->company_id;

        return array_merge(
            $this->companyRules(),
            [
                'brand_id' => ['nullable', 'exists:brands,id'],
                'assigned_to' => ['nullable', 'exists:users,id'],

                // Basic Info
                'title'        => ['required', 'string', 'max:255'],
                'manage_stock' => ['nullable'],
                'product_type' => ['nullable', 'string', Rule::in(['raw_material', 'semi_finished', 'finished', 'service'])],
                'slug'         => ['nullable', 'string', 'max:255', 'unique:products,slug'],
                'thumbnail'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
                'video_link'   => ['nullable', 'url'],

                // Categories
                'mega_category_ids'    => ['nullable', 'array'],
                'mega_category_ids.*'  => ['exists:mega_categories,id'],
                'sub_category_ids'     => ['nullable', 'array'],
                'sub_category_ids.*'   => ['exists:sub_categories,id'],
                'mini_category_ids'    => ['nullable', 'array'],
                'mini_category_ids.*'  => ['exists:mini_categories,id'],
                'extra_category_ids'   => ['nullable', 'array'],
                'extra_category_ids.*' => ['exists:extra_categories,id'],

                // Product Type
                'type'     => ['required', Rule::in(['single', 'variation'])],
                'sku_code' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('products')->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    }),
                ],

                // Single Product Fields
                'regular_price' => ['nullable', 'numeric', 'min:0'],
                'purchase_price' => ['nullable'],
                'discount_type' => ['nullable', Rule::in(['flat', 'percent'])],
                'discount'      => ['nullable', 'numeric', 'min:0'],
                'warehouse_info'                        => ['nullable', 'array'],
                'warehouse_info.*.warehouse_id'         => ['nullable', 'integer', 'exists:warehouses,id'],
                'warehouse_info.*.bin_id'               => ['nullable', 'integer', 'exists:bins,id'],
                'warehouse_info.*.quantity'             => ['nullable', 'numeric', 'min:0'],

                // Variation Product Fields
                'variations'                                     => ['required_if:type,variation', 'nullable', 'array', 'min:1'],
                'variations.*.attributes'                        => ['required_with:variations', 'array', 'min:1'],
                'variations.*.attributes.*.attribute_group_id'  => ['required', 'exists:attribute_groups,id'],
                'variations.*.attributes.*.attribute_value_id'  => ['required', 'exists:attribute_values,id'],
                'variations.*.sku'                               => ['nullable'],

                'variations.*.image'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
                'variations.*.gallery_images' => ['nullable', 'array'],
                'variations.*.gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
                'variations.*.regular_price'  => ['required', 'numeric', 'min:0'],
                'variations.*.purchase_price'  => ['nullable'],
                'variations.*.discount_type'  => ['nullable', Rule::in(['flat', 'percent'])],
                'variations.*.discount'       => ['nullable', 'numeric', 'min:0'],
                'variations.*.warehouse_info'                    => ['required', 'array', 'min:1'],
                'variations.*.warehouse_info.*.warehouse_id'     => ['required', 'integer', 'exists:warehouses,id'],
                'variations.*.warehouse_info.*.bin_id'           => ['nullable', 'integer', 'exists:bins,id'],
                'variations.*.warehouse_info.*.quantity'         => ['required', 'integer', 'min:0'],

                // Gallery Images
                'gallery_images'   => ['nullable', 'array'],
                'gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],

                // Description
                'short_description' => ['nullable', 'string'],
                'full_description'  => ['nullable', 'string'],

                // Purpose & Meta
                'purpose'          => ['required', 'string', 'max:255'],
                'meta_title'       => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string', 'max:255'],
                'meta_keywords'    => ['nullable','string', 'max:255'],
                'meta_keywords.*'  => ['nullable', 'string'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'title.required'           => 'Product title is required.',
                'slug.unique'              => 'This slug is already taken.',
                'thumbnail.required'       => 'Thumbnail is required.',
                'thumbnail.image'          => 'Thumbnail must be an image.',
                'thumbnail.max'            => 'Thumbnail size cannot exceed 2MB.',
                'gallery_images.*.image'   => 'All gallery files must be images.',
                'gallery_images.*.max'     => 'Gallery image size cannot exceed 2MB.',
                'warehouse_info.required_if'              => 'Warehouse information is required.',
                'warehouse_info.array'                    => 'Warehouse information must be an array.',
                'warehouse_info.*.warehouse_id.required_with' => 'Warehouse is required.',
                'warehouse_info.*.warehouse_id.integer'   => 'Warehouse ID must be an integer.',
                'warehouse_info.*.warehouse_id.exists'    => 'Selected warehouse does not exist.',
                'warehouse_info.*.bin_id.integer'         => 'Bin ID must be an integer.',
                'warehouse_info.*.bin_id.exists'          => 'Selected bin does not exist.',
                'warehouse_info.*.quantity.required_with' => 'Stock Quantity is required.',
                'warehouse_info.*.quantity.integer'       => 'Stock Quantity must be a whole number.',
                'warehouse_info.*.quantity.min'           => 'Stock Quantity cannot be negative.',
                'type.required'            => 'Product type is required.',
                'sku_code.unique'          => 'This SKU code already exists in your company.',
                'regular_price.required'   => 'Regular price is required.',
                'regular_price.min'        => 'Price must be at least 0.',
                'variations.*.sku.unique'  => 'One or more variation SKUs already exist in your company.',
            ]
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
