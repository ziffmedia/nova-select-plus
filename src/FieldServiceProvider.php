<?php

namespace RalphSchindler\NovaRelationMultiselect;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class FieldServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('relation-multiselect-field', __DIR__ . '/../dist/js/relation-multiselect-field.js');
            Nova::style('relation-multiselect-field', __DIR__ . '/../dist/css/relation-multiselect-field.css');
        });
    }

    public function register()
    {
        //
    }
}
