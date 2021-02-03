<?php

namespace App\Nova;

use App\State as StateModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use ZiffMedia\NovaSelectPlus\SelectPlus;

class Person extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Person';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'email',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('The name is required'),

            SelectPlus::make('States Lived In', 'statesLivedIn', State::class)
                ->optionsQuery(function (Builder $query) {
                    $query->where('name', 'NOT LIKE', 'C%');
                })
                // ->label(fn ($state) => $state->id . ' - ' . $state->name)
                // ->ajaxSearchable(true)
                // ->ajaxSearchable(fn ($query, $search) => $query->where('name', 'LIKE', "%{$search}%")->limit(2))
                ->placeholder('Type to search')
                ->help('This is a belongsToMany() relationship in the model'),

            SelectPlus::make('States Visited', 'statesVisited', State::class)
                ->usingIndexLabel(function ($models) {
                    $value = $models->take(1)->pluck('name');

                    if ($models->count() > 1) {
                        $value[] = '...';
                    }

                    return $value->implode(', ');
                })
                ->optionsQuery(function (Builder $query) {
                    $query->where('name', 'NOT LIKE', 'C%');
                })
                ->ajaxSearchable(function (Builder $query, $search) {
                    $query->where('name', 'LIKE', "%{$search}%")->limit(5);
                }, true)
                ->label(fn ($state) => $state->name . " <span class=\"text-xs\">({$state->code})</span>")
                ->reorderable('order')
                ->help('This is a belongsToMany() relationship with a pivot attribute for tracking order, and is ajax searchable.'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
