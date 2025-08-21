<?php

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'sku' => 'required|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0|gt:price',
            'weight' => 'nullable|integer|min:0',
            'dimensions' => 'nullable|string|max:50',
            'status' => ['required', Rule::in(['active', 'inactive', 'draft'])],
            'is_featured' => 'boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:100',
            'variants.*.sku' => 'required_with:variants|string|max:100|unique:product_variants,sku',
            'variants.*.price_adjustment' => 'nullable|numeric',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.sort_order' => 'nullable|integer|min:0',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB
            'meta_data' => 'nullable|array',
            'meta_data.seo' => 'nullable|array',
            'meta_data.seo.title' => 'nullable|string|max:60',
            'meta_data.seo.description' => 'nullable|string|max:160',
            'meta_data.seo.keywords' => 'nullable|string|max:255',
            'meta_data.specs' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'sku.required' => 'SKU produk wajib diisi.',
            'sku.unique' => 'SKU sudah digunakan produk lain.',
            'price.required' => 'Harga produk wajib diisi.',
            'price.min' => 'Harga tidak boleh negatif.',
            'compare_price.gt' => 'Harga pembanding harus lebih besar dari harga jual.',
            'category_ids.*.exists' => 'Kategori yang dipilih tidak valid.',
            'variants.*.sku.unique' => 'SKU varian sudah digunakan.',
            'images.*.image' => 'File harus berupa gambar.',
            'images.*.max' => 'Ukuran gambar maksimal 5MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name') && !$this->has('slug')) {
            $this->merge([
                'slug' => \Str::slug($this->name)
            ]);
        }

        if (!$this->has('is_featured')) {
            $this->merge(['is_featured' => false]);
        }

        if (!$this->has('status')) {
            $this->merge(['status' => 'active']);
        }
    }
}