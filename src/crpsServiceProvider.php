<?php

namespace imonroe\crps;
use imonroe\crps\Aspect;
use imonroe\crps\AspectType;
use imonroe\crps\Subject;
use imonroe\crps\SubjectType;
use Illuminate\Support\ServiceProvider;

class crpsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //parent::boot();
        // Migrations:
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        // Views:
        //$this->loadViewsFrom(__DIR__.'/path/to/views', 'courier');
        //$this->publishes([
        //	__DIR__.'/path/to/views' => resource_path('views/vendor/courier'),
        //]);

        // Routes:
        $this->loadRoutesFrom(__DIR__.'/Http/routes.php');

    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {

    }
}