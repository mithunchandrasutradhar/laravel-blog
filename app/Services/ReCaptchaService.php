<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReCaptchaService
{
    /**
     * Google reCAPTCHA v3 verification endpoint.
     */
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Minimum score threshold for a reCAPTCHA v3 response.
     * Scores range from 0.0 (bot) to 1.0 (human).
     */
    private const MIN_SCORE = 0.5;

    /**
     * The secret key configured in config/blog.php.
     */
    private string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('blog.recaptcha_secret_key', '');
    }

    // -------------------------------------------------------------------------
    // Verification
    // -------------------------------------------------------------------------

    /**
     * Verify a reCAPTCHA v3 token returned by the client-side widget.
     *
     * @param  string       $token   The `g-recaptcha-response` token.
     * @param  float|null   $minScore  Override the default score threshold.
     * @param  string|null  $action    Expected action name (optional, but recommended).
     * @return bool  True when the token is valid and the score meets the threshold.
     */
    public function verify(string $token, ?float $minScore = null, ?string $action = null): bool
    {
        if (empty($this->secretKey)) {
            Log::warning('ReCaptchaService: secret key is not configured.');
            return false;
        }

        if (empty(trim($token))) {
            return false;
        }

        $minScore ??= self::MIN_SCORE;

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post(self::VERIFY_URL, [
                    'secret'   => $this->secretKey,
                    'response' => $token,
                    'remoteip' => request()->ip(),
                ]);

            if (! $response->successful()) {
                Log::error('ReCaptchaService: HTTP request failed.', [
                    'status' => $response->status(),
                ]);
                return false;
            }

            $data = $response->json();

            if (! ($data['success'] ?? false)) {
                Log::info('ReCaptchaService: verification failed.', [
                    'error-codes' => $data['error-codes'] ?? [],
                ]);
                return false;
            }

            // Validate score.
            $score = (float) ($data['score'] ?? 0);
            if ($score < $minScore) {
                Log::info('ReCaptchaService: score below threshold.', [
                    'score'    => $score,
                    'required' => $minScore,
                ]);
                return false;
            }

            // Optionally validate the expected action.
            if ($action !== null && ($data['action'] ?? '') !== $action) {
                Log::info('ReCaptchaService: action mismatch.', [
                    'expected' => $action,
                    'received' => $data['action'] ?? '',
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('ReCaptchaService: unexpected error.', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Return the site key for embedding in Blade views.
     */
    public function siteKey(): string
    {
        return config('blog.recaptcha_site_key', '');
    }

    /**
     * Determine whether reCAPTCHA is properly configured (both keys present).
     */
    public function isConfigured(): bool
    {
        return ! empty(config('blog.recaptcha_site_key'))
            && ! empty(config('blog.recaptcha_secret_key'));
    }
}
