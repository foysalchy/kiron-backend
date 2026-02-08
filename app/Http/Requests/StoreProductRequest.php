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

    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'brand_id' => ['nullable', 'exists:brands,id'],

                // Basic Info
                'title' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
                'thumbnail' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
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

                // Product Type
                'type' => ['required', Rule::in(['single', 'variation'])],
                'sku_codes' => ['nullable', 'array'],
                'sku_codes.*' => ['string', 'max:255'],

                // Single Product Fields (only validated when type='single')
                'regular_price' => ['required_if:type,single', 'nullable', 'numeric', 'min:0'],
                'discount_type' => ['nullable', Rule::in(['flat', 'percent'])],
                'discount' => ['nullable', 'numeric', 'min:0'],
                'warehouse_info' => ['required_if:type,single', 'nullable', 'array'],
                'warehouse_info.*.warehouse_id' => ['required_with:warehouse_info', 'integer', 'exists:warehouses,id'],
                'warehouse_info.*.bin_id' => ['nullable', 'integer', 'exists:cells,id'],
                'warehouse_info.*.quantity' => ['required_with:warehouse_info', 'integer', 'min:0'],

                // Variation Product Fields (only validated when type='variation')
                'variations' => ['required_if:type,variation', 'nullable', 'array', 'min:1'],
                'variations.*.attributes' => ['required_with:variations', 'array', 'min:1'],
                'variations.*.attributes.*.attribute_group_id' => ['required', 'exists:attribute_groups,id'],
                'variations.*.attributes.*.attribute_value_id' => ['required', 'exists:attribute_values,id'],
                'variations.*.sku' => ['nullable', 'string', 'max:255'],
                'variations.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
                'variations.*.regular_price' => ['required', 'numeric', 'min:0'],
                'variations.*.discount_type' => ['nullable', Rule::in(['flat', 'percent'])],
                'variations.*.discount' => ['nullable', 'numeric', 'min:0'],
                'variations.*.warehouse_info' => ['required', 'array', 'min:1'],
                'variations.*.warehouse_info.*.warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
                'variations.*.warehouse_info.*.bin_id' => ['nullable', 'integer', 'exists:bins,id'],
                'variations.*.warehouse_info.*.quantity' => ['required', 'integer', 'min:0'],
                // Gallery Images
                'gallery_images' => ['nullable', 'array'],
                'gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
                // Description
                'short_description' => ['nullable', 'string'],
                'full_description' => ['nullable', 'string'],
                // Purpose
                'purpose' => ['required', 'string', 'max:255'],
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
                'thumbnail.required' => 'Thumbnail  is required',
                'thumbnail.image' => 'Thumbnail must be an image',
                'thumbnail.max' => 'Thumbnail size cannot exceed 2MB',
                'gallery_images.*.image' => 'All gallery files must be images',
                'gallery_images.*.max' => 'Gallery image size cannot exceed 2MB',
                'type.required' => 'Product type is required',

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
