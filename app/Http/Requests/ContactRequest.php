<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
        ];

        if (settings('recaptcha_secret_key')) {
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
                return; // already caught by required rule
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
