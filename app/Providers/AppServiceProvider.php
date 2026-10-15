<?php

namespace App\Providers;

use App\Support\PowerGrid\CaseInsensitiveSearchHandler;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use PowerComponents\LivewirePowerGrid\DataSource\Processors\Database\Handlers\SearchHandlerContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SearchHandlerContract::class, function ($app, array $params) {
            return new CaseInsensitiveSearchHandler($params['component']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $path = parse_url(config('app.url'), PHP_URL_PATH) ?: '';
        $prefix = $path ? '/'.trim($path, '/') : '';

        Livewire::setScriptRoute(function ($handle) use ($prefix) {
            return Route::get($prefix.'/livewire/livewire.js', $handle);
        });

        Livewire::setUpdateRoute(function ($handle) use ($prefix) {
            return Route::post($prefix.'/livewire/update', $handle)
                ->middleware('web');
        });
    }
}
