<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // This project serves from repo root (index.php + assets + uploads),
        // while Laravel lives in /app. DomPDF needs a resolvable public path.
        $webRoot = dirname($this->app->basePath());
        if (is_dir($webRoot) && is_file($webRoot . DIRECTORY_SEPARATOR . 'index.php')) {
            $this->app->usePublicPath($webRoot);
        } else {
            $laravelPublic = $this->app->basePath('public');
            if (!is_dir($laravelPublic)) {
                @mkdir($laravelPublic, 0755, true);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Keep DomPDF base path in sync with Laravel public_path()
        if (!config('dompdf.public_path')) {
            config(['dompdf.public_path' => public_path()]);
        }
    }
}
