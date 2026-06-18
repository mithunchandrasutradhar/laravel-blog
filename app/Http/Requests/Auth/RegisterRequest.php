<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms'    => ['required', 'accepted'],
        ];

        if (settings('recaptcha_site_key') && settings('recaptcha_secret_key')) {
            $rules['g-recaptcha-response'] = ['required'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
        ];
    }

    public function withValidator($validator): void
    {
        $secretKey = settings('recaptcha_secret_key');

        if (! $secretKey) {
            return;
        }

        $validator->after(function ($validator) use ($secretKey) {
            $token = $this->input('g-recaptcha-response');

            if (! $token) {
                return;
            }

            try {
                $result = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret'   => $secretKey,
                    'response' => $token,
                    'remoteip' => $this->ip(),
                ]);

                if (! $result->successful() || ! $result->json('success')) {
                    $validator->errors()->add(
                        'g-recaptcha-response',
                        'reCAPTCHA verification failed. Please try again.'
                    );
                }
            } catch (\Throwable) {
                $validator->errors()->add(
                    'g-recaptcha-response',
                    'Could not verify reCAPTCHA. Please try again later.'
                );
            }
        });
    }
}
