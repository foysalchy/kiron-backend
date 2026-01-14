<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'exists:companies,id'],
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

            // Gallery Images
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            // Description
            'short_description' => ['nullable', 'string'],
            'full_description' => ['nullable', 'string'],

            // Product Type
            'type' => ['required', Rule::in(['single', 'variation'])],
            'sku_codes' => ['nullable', 'array'],
            'sku_codes.*' => ['string', 'max:255'],

            // Stock
            'stock_status' => ['required', Rule::in(['in_stock', 'out_of_stock'])],
            'stock_quantity' => ['required_if:stock_status,in_stock', 'integer', 'min:0'],

            // Pricing
            'regular_price' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['nullable', Rule::in(['flat', 'percent'])],
            'discount' => ['nullable', 'numeric', 'min:0'],

            // Purpose
            'purpose' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'title.required' => 'Product title is required',
            'slug.unique' => 'This slug is already taken',
            'thumbnail.required' => 'Thumbnail  is required',
            'thumbnail.image' => 'Thumbnail must be an image',
            'thumbnail.max' => 'Thumbnail size cannot exceed 2MB',
            'gallery_images.*.image' => 'All gallery files must be images',
            'gallery_images.*.max' => 'Gallery image size cannot exceed 2MB',
            'type.required' => 'Product type is required',
            'stock_status.required' => 'Stock status is required',
            'stock_quantity.required_if' => 'Stock quantity is required when product is in stock',
            'regular_price.required' => 'Regular price is required',
            'regular_price.min' => 'Price must be at least 0',
        ];
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
