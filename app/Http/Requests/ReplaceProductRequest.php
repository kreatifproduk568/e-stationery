<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ReplaceProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'category_id' => [
                'required',
                'uuid',
                'exists:categories,id',
            ],

            'brand_id' => [
                'required',
                'uuid',
                'exists:brands,id',
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->ignore($productId, 'id'),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'slug')
                    ->ignore($productId, 'id'),
            ],

            'description' => [
                'present',
                'nullable',
                'string',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'is_featured' => [
                'required',
                'boolean',
            ],
        ];
    }
}
