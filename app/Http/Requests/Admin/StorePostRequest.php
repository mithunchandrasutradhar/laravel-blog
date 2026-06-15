<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255', 'unique:posts,slug'],
            'category_ids'      => ['required', 'array', 'min:1'],
            'category_ids.*'    => ['integer', 'exists:categories,id'],
            'user_id'           => ['nullable', 'integer', 'exists:users,id'],
            'is_featured'       => ['nullable', 'boolean'],
            'allow_comments'    => ['nullable', 'boolean'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'content'           => ['required', 'string'],
            'featured_image'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status'            => ['required', 'in:draft,published,scheduled,archived'],
            'published_at'      => ['nullable', 'date'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string', 'max:320'],
            'canonical_url'     => ['nullable', 'url', 'max:2048'],
            'tags'              => ['nullable', 'array'],
            'tags.*'            => ['required'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured'       => $this->boolean('is_featured'),
            'allow_comments'    => $this->boolean('allow_comments', true),
            'short_description' => $this->input('short_description') ?? $this->input('excerpt'),
        ]);
    }
}
