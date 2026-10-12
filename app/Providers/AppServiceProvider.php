<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->scoped(\App\Services\WebsiteSettings::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Support\Facades\RateLimiter::for('website-admin-login', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip());
        });

        View::composer(['layout.user', 'layout.website-admin', 'user.*', 'admin.*'], function ($view) {
            $view->with('website', app(\App\Services\WebsiteSettings::class)->all());
        });

        View::composer('user.partials.header', function ($view) {
            $locations = DB::table('location')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            $view->with('locations', $locations);

            $promotions = DB::table('promotion')
                ->whereDate('start_date', '<=', now())
                ->where(function ($q) {
                    $q->whereDate('end_date', '>=', now())
                    ->orWhereNull('end_date');
                })
                ->orderBy('name')
                ->get();

            $view->with('promotions', $promotions);
        });
    }
}
