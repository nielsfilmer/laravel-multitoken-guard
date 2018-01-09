<?php namespace NielsFilmer\MultiTokenGuard;

use Illuminate\Support\ServiceProvider;
use NielsFilmer\MultiTokenGuard\Guards\MultiTokenGuard;
use Illuminate\Support\Facades\Auth;


class MultiTokenGuardServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Register the config and view paths
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../migrations' => base_path('database/migrations'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // add custom guard
        Auth::extend('multitoken', function ($app, $name, array $config) {
            return new MultiTokenGuard(Auth::createUserProvider($config['provider']), $app->make('request'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
