<?php

namespace App\Http\Requests\Author;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $this->user()->isAdmin()
            || ($this->user()->isAuthor() && $post->user_id === $this->user()->id);
    }

    public function rules(): array
    {
        $postId = $this->route('post')->id;

        return [
            'title'               => ['required', 'string', 'max:255'],
            'slug'                => ['nullable', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($postId)],
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
