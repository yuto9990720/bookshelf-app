<?php

namespace App\Providers;

use App\Models\Review;
use App\Policies\ReviewLikePolicy;
use Illuminate\Support\Facades\Gate;
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
            return Password::min(8);
        });

        Gate::define('like', [ReviewLikePolicy::class, 'like']);
    }
}