<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('advertisements.update') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'type'      => ['required', 'in:adsense,banner'],
            'position'  => ['required', 'in:header,sidebar,in-article,footer'],
            'code'      => ['nullable', 'string'],
            'image'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'url'       => ['nullable', 'url', 'max:2048'],
            'is_active' => ['boolean'],
        ];
    }
}
