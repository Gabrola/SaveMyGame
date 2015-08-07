<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use LeagueHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('region', function($attribute, $value, $parameters) {
            return LeagueHelper::regionExists($value);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('league', function(){
            return new \App\Helpers\LeagueHelper();
        });
    }
}
