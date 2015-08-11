<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use LeagueHelper;
use View;

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

        View::composer('errors.404', function($view){
            $view->with('pageName', '404');
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
