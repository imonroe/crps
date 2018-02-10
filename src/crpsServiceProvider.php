<?php

namespace imonroe\crps;

use imonroe\crps\Aspect;
use imonroe\crps\AspectType;
use imonroe\crps\Subject;
use imonroe\crps\SubjectType;
use imonroe\crps\SearchRegistry;
use imonroe\crps\SubjectSearchProvider;
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

        $preferences_registry = app()->make('ApplicationPreferencesRegistry');
        // $animal_pref = ['preference' => 'fav_animal', 'preference_label' => 'Favorite Animal', 'field_type' => 'text', 'default_value'=>'none'];
        // $test_pref = ['preference' => 'test_permission', 'preference_label' => 'Test Permission', 'field_type' => 'checkbox', 'default_value' => FALSE ];
        // $preferences_registry->register_preference($animal_pref);
        // $preferences_registry->register_preference($test_pref);  

        $search_registry = app()->make('SearchRegistry');
        $subject_searcher = new SubjectSearchProvider;
        $aspect_searcher = new AspectSearchProvider;
        $search_registry->register_search_class($subject_searcher, 98);
        $search_registry->register_search_class($aspect_searcher, 99);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('SearchRegistry', function() {
            return new \imonroe\crps\SearchRegistry;
        });

        $this->app->singleton('ApplicationPreferencesRegistry', function() {
            return new \imonroe\crps\ApplicationPreferencesRegistry;
        });

    }
}
