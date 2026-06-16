<?php

namespace App\Http\Requests\Author;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAuthor() || $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title'               => ['required', 'string', 'max:255'],
            'slug'                => ['nullable', 'string', 'max:255', 'unique:posts,slug'],
            'category_ids'        => ['required', 'array', 'min:1'],
            'category_ids.*'      => ['integer', 'exists:categories,id'],
            'short_description'   => ['nullable', 'string', 'max:500'],
            'content'             => ['required', 'string'],
            'featured_image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'featured_image_path' => ['nullable', 'string'],
            'status'              => ['required', 'in:draft,published,scheduled'],
            'published_at'        => ['nullable', 'date'],
            'is_featured'         => ['nullable', 'boolean'],
            'allow_comments'      => ['nullable', 'boolean'],
            'meta_title'          => ['nullable', 'string', 'max:255'],
            'meta_description'    => ['nullable', 'string', 'max:320'],
            'meta_keywords'       => ['nullable', 'string', 'max:500'],
            'canonical_url'       => ['nullable', 'url', 'max:500'],
            'tags'                => ['nullable', 'array'],
            'tags.*'              => ['required'],
        ];
    }
}
