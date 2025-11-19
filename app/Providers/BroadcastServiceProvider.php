<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register broadcasting routes for private channels
        Broadcast::routes([
            'prefix' => 'api', 
            'middleware' => ['auth:api'], // matches your API auth
        ]);

        // Load channel authorization callbacks
        require base_path('routes/channels.php');
    }
}
