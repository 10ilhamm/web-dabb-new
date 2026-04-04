<?php

namespace App\Providers;

use App\Models\Feature;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(function () {
            return Password::min(10)
                ->max(100)
                ->mixedCase()
                ->symbols()
                ->uncompromised();
        });

        View::composer('navbar', function ($view) {
            $view->with('navFeatures', Feature::whereNull('parent_id')
                ->with('subfeatures.subfeatures')
                ->orderBy('order')
                ->get());
        });
    }
}
