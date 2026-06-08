<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
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
        $tagId = $this->route('tag')->id;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('tags', 'name')->ignore($tagId)],
            'slug' => ['nullable', 'string', 'max:100', Rule::unique('tags', 'slug')->ignore($tagId)],
        ];
    }
}
