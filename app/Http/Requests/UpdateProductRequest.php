<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    
public function rules(): array
{
    $productId = $this->route('id');

//     logger()->info('Diagnostik UpdateProductRequest', [
//     'route_id' => $productId,
//     'route_parameters' => $this->route()->parameters(),
// ]);

    return [
        'category_id' => [
            'sometimes',
            'required',
            'uuid',
            'exists:categories,id',
        ],

        'brand_id' => [
            'sometimes',
            'required',
            'uuid',
            'exists:brands,id',
        ],

        'sku' => [
            'sometimes',
            'required',
            'string',
            'max:100',
            Rule::unique('products', 'sku')
                ->ignore($productId, 'id'),
        ],

        'name' => [
            'sometimes',
            'required',
            'string',
            'max:255',
        ],

        'slug' => [
            'sometimes',
            'required',
            'string',
            'max:255',
            Rule::unique('products', 'slug')
                ->ignore($productId, 'id'),
        ],

        'description' => [
            'sometimes',
            'nullable',
            'string',
        ],

        'is_active' => [
            'sometimes',
            'boolean',
        ],

        'is_featured' => [
            'sometimes',
            'boolean',
        ],
    ];
}

}
