<?php

namespace App\Nova;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use ZiffMedia\NovaSelectPlus\SelectPlus;

class Person extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Person::class;

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
        'id',
    ];

    public static $preventFormAbandonment = true;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
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

                // this is an example of hooking into the collection to result mapping, and doing an extra lookup for additional information

                // ->withMapToSelectionValues(function (Collection $collection) {
                //     $counts = DB::table('states')
                //         ->select(['id', DB::raw('length(name) as count')])
                //         ->whereIn('id', $collection->pluck('model.id'))
                //         ->get()
                //         ->mapWithKeys(fn ($item) => [$item->id => $item->count]);
                //
                //     return $collection->map(function ($result) use ($counts) {
                //         $result['label'] = $result['model']->name . ' (' . $counts[$result['model']->id] . ')';
                //         return $result;
                //     });
                // })
                ->label(fn ($state) => $state->name." <span class=\"text-xs\">({$state->code})</span>")
                ->reorderable('order')
                ->help('This is a belongsToMany() relationship with a pivot attribute for tracking order, and is ajax searchable.'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
