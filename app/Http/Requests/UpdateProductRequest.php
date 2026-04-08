<?php

namespace App\Http\Requests;

use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductRequest extends UpdateBaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('id');
        return array_merge(
            $this->companyRules(),
            [
                'brand_id' => ['nullable', 'exists:brands,id'],

                // Basic Info
                'title' => ['sometimes', 'required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
                'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
                'video_link' => ['nullable', 'url'],

                // Categories
                'mega_category_ids' => ['nullable', 'array'],
                'mega_category_ids.*' => ['exists:mega_categories,id'],
                'sub_category_ids' => ['nullable', 'array'],
                'sub_category_ids.*' => ['exists:sub_categories,id'],
                'mini_category_ids' => ['nullable', 'array'],
                'mini_category_ids.*' => ['exists:mini_categories,id'],
                'extra_category_ids' => ['nullable', 'array'],
                'extra_category_ids.*' => ['exists:extra_categories,id'],

                // Gallery Images (new images to add)
                'gallery_images' => ['nullable', 'array'],
                'gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
                'deleted_gallery_ids' => ['nullable', 'array'],

                // Description
                'short_description' => ['nullable', 'string'],
                'full_description' => ['nullable', 'string'],

                // Product Type
                'type' => ['sometimes', 'required', Rule::in(['single', 'variation'])],



                // Stock
                'stock_status' => ['sometimes', 'required', Rule::in(['in_stock', 'out_of_stock'])],
                'stock_quantity' => ['nullable', 'integer', 'min:0'],

                // Single Product Fields (only validated when type='single')
                'regular_price' => ['required_if:type,single', 'nullable', 'numeric', 'min:0'],
                'purchase_price' => ['nullable', 'numeric', 'min:0'],
                'discount_type' => ['nullable', Rule::in(['flat', 'percent'])],
                'discount' => ['nullable', 'numeric', 'min:0'],
                'warehouse_info' => ['required_if:type,single', 'nullable', 'array'],
                'warehouse_info.*.warehouse_id' => ['required_with:warehouse_info', 'integer', 'exists:warehouses,id'],
                'warehouse_info.*.bin_id' => ['nullable', 'integer', 'exists:bins,id'],
                'warehouse_info.*.quantity' => ['required_with:warehouse_info', 'integer', 'min:0'],

                // Variation Product Fields (only validated when type='variation')
                'variations' => ['required_if:type,variation', 'nullable', 'array', 'min:1'],
                'variations.*.attributes' => ['required_with:variations', 'array', 'min:1'],
                'variations.*.attributes.*.attribute_group_id' => ['required', 'exists:attribute_groups,id'],
                'variations.*.attributes.*.attribute_value_id' => ['required', 'exists:attribute_values,id'],
                'variations.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
                'variations.*.gallery_images' => ['nullable', 'array'],
                'variations.*.gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
                'variations.*.deleted_gallery_ids' => ['nullable', 'array'],
                'variations.*.deleted_gallery_ids.*' => ['integer'],
                'variations.*.regular_price' => ['required', 'numeric', 'min:0'],
                'variations.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
                'variations.*.discount_type' => ['nullable', Rule::in(['flat', 'percent'])],
                'variations.*.discount' => ['nullable', 'numeric', 'min:0'],
                'variations.*.warehouse_info' => ['required', 'array', 'min:1'],
                'variations.*.warehouse_info.*.warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
                'variations.*.warehouse_info.*.bin_id' => ['nullable', 'integer', 'exists:bins,id'],
                'variations.*.warehouse_info.*.quantity' => ['required', 'integer', 'min:0'],
                // Purpose
                'purpose' => ['sometimes', 'required', 'string', 'max:255'],
                'meta_title' => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string'],
                'meta_keywords' => ['nullable', 'array'],
                'meta_keywords.*' => ['nullable', 'string',],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'title.required' => 'Product title is required',
                'slug.unique' => 'This slug is already taken',
                'thumbnail.image' => 'Thumbnail must be an image',
                'thumbnail.max' => 'Thumbnail size cannot exceed 2MB',
                'gallery_images.*.image' => 'All gallery files must be images',
                'gallery_images.*.max' => 'Gallery image size cannot exceed 2MB',
                'type.required' => 'Product type is required',
                'stock_status.required' => 'Stock status is required',
                'regular_price.required' => 'Regular price is required',
                'regular_price.min' => 'Price must be at least 0',
            ]
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
