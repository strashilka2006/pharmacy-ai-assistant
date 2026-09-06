<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->configureRateLimiters();
    }

    /**
     * Лимиты, которые в оригинале жили в $_SESSION вручную:
     * ai_log/ai_last в chat_api.php и code_log/code_last в register.php.
     */
    private function configureRateLimiters(): void
    {
        RateLimiter::for('ai-consultant', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return [
                Limit::perMinute(config('pharmacy.ollama.rate_limit.per_minute'))->by($key),
                Limit::perHour(config('pharmacy.ollama.rate_limit.per_hour'))->by($key),
            ];
        });

        RateLimiter::for('verification-code', function (Request $request) {
            return [
                Limit::perMinute(1)->by($request->input('email') . $request->ip()),
                Limit::perHour(config('pharmacy.email_verification.max_per_hour'))->by($request->ip()),
            ];
        });
    }
}
