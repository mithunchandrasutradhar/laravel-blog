<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')->id;

        return [
            'name'             => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($categoryId)],
            'slug'             => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description'      => ['nullable', 'string', 'max:1000'],
            'image'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'color'            => ['nullable', 'string', 'max:20'],
            'icon'             => ['nullable', 'string', 'max:60'],
            'parent_id'        => ['nullable', 'integer', 'exists:categories,id', Rule::notIn([$categoryId])],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
        ];
    }
}
