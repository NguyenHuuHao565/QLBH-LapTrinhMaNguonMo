<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\Cart;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Paginator::useBootstrapFive();

        // Danh mục cho menu header/footer, chỉ truy vấn một lần mỗi request
        View::composer(['components.site-header', 'components.site-footer'], function ($view) {
            $view->with('navCategories', once(fn () => Category::active()->orderBy('id')->get()));
        });

        View::composer('components.site-header', function ($view) {
            $view->with('cartCount', app(Cart::class)->count());
        });
    }
}
