<?php

namespace Romero\PerfectMoney;

use Illuminate\Support\ServiceProvider;

class LaravelZibalServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'perfectmoney',
            function ($app) {
                return new PerfectMoney();
            }
        );

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'perfectmoney');
    }

    /**
     * Publish the plugin configuration.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__.'/../config/config.php' => config_path('perfectmoney.php'),
                ],
                'config'
            );
        }
    }
}