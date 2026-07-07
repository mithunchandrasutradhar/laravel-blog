<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
        // reCAPTCHA temporarily disabled on login for local development
        // (site key isn't verified for this domain). Re-enable by restoring
        // the settings()-gated rule below.
        $rules = [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
        ];
    }

    // reCAPTCHA temporarily disabled on login for local development (site key
    // isn't verified for this domain). Restore this method's body to
    // re-enable:
    //
    // public function withValidator($validator): void
    // {
    //     $secretKey = settings('recaptcha_secret_key');
    //
    //     if (! $secretKey) {
    //         return;
    //     }
    //
    //     $validator->after(function ($validator) use ($secretKey) {
    //         $token = $this->input('g-recaptcha-response');
    //
    //         if (! $token) {
    //             return;
    //         }
    //
    //         try {
    //             $result = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
    //                 'secret'   => $secretKey,
    //                 'response' => $token,
    //                 'remoteip' => $this->ip(),
    //             ]);
    //
    //             if (! $result->successful() || ! $result->json('success')) {
    //                 $validator->errors()->add(
    //                     'g-recaptcha-response',
    //                     'reCAPTCHA verification failed. Please try again.'
    //                 );
    //             }
    //         } catch (\Throwable) {
    //             $validator->errors()->add(
    //                 'g-recaptcha-response',
    //                 'Could not verify reCAPTCHA. Please try again later.'
    //             );
    //         }
    //     });
    // }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Block inactive or suspended accounts immediately after credential check
        $user = Auth::user();
        if (! $user->isActive()) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            $message = $user->status === 'suspended'
                ? 'Your account has been suspended. Please contact support.'
                : 'Your account is currently inactive. Please contact support.';

            throw ValidationException::withMessages(['email' => $message]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
