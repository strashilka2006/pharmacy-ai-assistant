<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'stock' => ['required', 'integer', 'min:0'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'label' => ['nullable', Rule::in(array_keys(Product::LABELS))],
            'prescription' => ['boolean'],
            'supplier' => ['nullable', 'string', 'max:255'],

            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'long_description' => ['nullable', 'string'],
            'usage_info' => ['nullable', 'string'],
            'indications' => ['nullable', 'string'],
            'composition' => ['nullable', 'string'],
            'contraindications' => ['nullable', 'string'],
            'drug_interactions' => ['nullable', 'string'],
            'overdose' => ['nullable', 'string'],

            'photo' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:8192'],
            'photo_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'prescription' => $this->boolean('prescription'),
            'price' => (float) str_replace([' ', ','], ['', '.'], (string) $this->input('price')),
        ]);
    }
}
