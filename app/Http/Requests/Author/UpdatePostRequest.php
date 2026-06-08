<?php

namespace App\Http\Requests\Author;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('post');

        // Author may only update their own posts; admins can update any post
        return $this->user()->isAdmin()
            || ($this->user()->isAuthor() && $post->user_id === $this->user()->id);
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $postId = $this->route('post')->id;

        return [
            'title'             => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($postId)],
            'category_id'       => ['required', 'integer', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'content'           => ['required', 'string'],
            'featured_image'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status'            => ['required', 'in:draft,published'],
            'published_at'      => ['nullable', 'date'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string', 'max:320'],
            'tags'              => ['nullable', 'array'],
            'tags.*'            => ['required'],
        ];
    }
}
