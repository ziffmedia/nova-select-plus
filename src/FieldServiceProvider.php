<?php

namespace ZiffMedia\NovaSelectPlus;

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Laravel\Nova\Resource;

class FieldServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('select-plus-field', __DIR__ . '/../dist/js/field.js');
            Nova::style('select-plus-field', __DIR__ . '/../dist/css/field.css');
        });

        Route::group(
            ['middleware' => 'nova', 'prefix' => 'nova-vendor/select-plus', 'namespace' => __NAMESPACE__],
            function ($route) {
                $route->get('/{resource}/{relationship}', 'Controller@options');
            }
        );
    }

    public function register()
    {
        //
    }
}
