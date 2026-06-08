<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $guestRules = auth()->check() ? [] : [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ];

        return array_merge($guestRules, [
            'post_id'   => ['required', 'integer', 'exists:posts,id'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'body'      => ['required', 'string', 'min:3', 'max:2000'],
        ]);
    }
}
