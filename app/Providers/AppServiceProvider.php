<?php

namespace App\Providers;

use App\Services\PdfImport\Contracts\PdfLayoutExtractorInterface;
use App\Services\PdfImport\Extractors\PythonPdfLayoutExtractor;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PdfLayoutExtractorInterface::class, PythonPdfLayoutExtractor::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
