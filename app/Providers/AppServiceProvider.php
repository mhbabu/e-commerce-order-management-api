<?php

namespace App\Providers;

use App\Console\Commands\ProductReIndexingForElasticSearching;
use App\Events\LowStockAlert;
use App\Events\OrderStatusChanged;
use App\Jobs\LowStockNotification;
use App\Listeners\GenerateInvoice;
use App\Listeners\SendOrderEmail;
use App\Models\Product;
use App\Observers\Product\ProductObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;


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

         RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        
        //EVETS LISTENERS
        Event::listen(OrderStatusChanged::class, SendOrderEmail::class);
        Event::listen(OrderStatusChanged::class, GenerateInvoice::class);  // when product delivered
        // Event::listen(LowStockAlert::class, LowStockNotification::class); 

        //OBSERVERS
        Product::observe(ProductObserver::class);

        //COMMANDS LINE
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProductReIndexingForElasticSearching::class,
            ]);
        }
    }
}
