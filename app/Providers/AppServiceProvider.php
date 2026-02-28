<?php

namespace App\Providers;

use App\Domain\ClothingAdvice\CategoryMatcherStrategy;
use App\Domain\ClothingAdvice\KeywordBasedCategoryMatcher;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            CategoryMatcherStrategy::class,
            KeywordBasedCategoryMatcher::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
    }
}
