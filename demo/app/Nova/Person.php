<?php

namespace App\Nova;

use App\Models\State as StateModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
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

            SelectPlus::make('State Born In', 'state_born_in')
                ->options(StateModel::class)
                ->ajaxSearchable(true)
                ->maxSelections(1)
            ,

            SelectPlus::make('State Parents Born In', 'state_parents_born_in')
                ->options([
                    'Florida',
                    'Louisiana',
                    'Texas'
                ])
                ->maxSelections(2)
            ,

            SelectPlus::make('Favorite State', 'favoriteState'),

            SelectPlus::make('States Visited', 'statesVisited')
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

            Select::make('Only Certain States', 'onlyCertainStates')
                ->options([
                    'Yes' => 'Yes',
                    'No' => 'No'
                ])
                ->help('Choose "Yes" to filter states that start with "L"'),

            SelectPlus::make('States Lived In', 'statesLivedIn')
                ->dependsOn(
                    ['onlyCertainStates'],
                    function (SelectPlus $field, NovaRequest $request, FormData $formData) {
                        // Demonstrate access to both Request and FormData objects
                        if ($formData->onlyCertainStates == 'Yes') {
                            $field->optionsQuery(
                                fn ($query) => $query->where('name', 'LIKE', 'L%')
                            );
                            
                            // You can also access the request object for additional context
                            if ($request->has('resourceId')) {
                                // Could modify behavior based on the resource being edited
                                $field->help('Filtered to states starting with "L" for resource ID: ' . $request->get('resourceId'));
                            }
                        } else {
                            // Reset to show all states when not filtered
                            $field->help('Showing all available states');
                        }
                    }
                )
                // ->optionsQuery(function (Builder $query) {
                //     $query->where('name', 'LIKE', 'C%');
                // })
                // ->label(fn ($state) => $state->id . ' - ' . $state->name)
                // ->ajaxSearchable(true)
                ->ajaxSearchable(fn ($query, $search) => $query->where('name', 'LIKE', "%{$search}%")->limit(2))
                ->placeholder('Type to search')
                ->help('This is a belongsToMany() relationship in the model'),

            Select::make('Region', 'region')
                ->options([
                    'west' => 'West Coast',
                    'east' => 'East Coast',
                    'central' => 'Central',
                ])
                ->help('Select a region to filter available cities'),

            SelectPlus::make('Cities Visited', 'cities_visited')
                ->options(\App\Models\City::class)
                ->dependsOn(
                    ['region', 'state_born_in'],
                    function (SelectPlus $field, NovaRequest $request, FormData $formData) {
                        // Example of depending on multiple fields
                        $region = $formData->region;
                        $stateBornIn = $formData->state_born_in;
                        
                        if ($region && $stateBornIn) {
                            $field->optionsQuery(function ($query) use ($region, $stateBornIn) {
                                // Filter cities based on both region and state
                                if ($region === 'west') {
                                    $query->whereHas('state', function ($q) {
                                        $q->whereIn('code', ['CA', 'WA', 'OR']);
                                    });
                                } elseif ($region === 'east') {
                                    $query->whereHas('state', function ($q) {
                                        $q->whereIn('code', ['NY', 'FL', 'MA']);
                                    });
                                }
                                
                                // Further filter by birth state if it's a model
                                if (is_numeric($stateBornIn)) {
                                    $query->whereHas('state', function ($q) use ($stateBornIn) {
                                        $q->where('id', $stateBornIn);
                                    });
                                }
                            });
                            
                            $field->help("Showing cities in {$region} region" . ($stateBornIn ? " related to your birth state" : ""));
                        } elseif ($region) {
                            $field->optionsQuery(function ($query) use ($region) {
                                if ($region === 'west') {
                                    $query->whereHas('state', function ($q) {
                                        $q->whereIn('code', ['CA', 'WA', 'OR']);
                                    });
                                } elseif ($region === 'east') {
                                    $query->whereHas('state', function ($q) {
                                        $q->whereIn('code', ['NY', 'FL', 'MA']);
                                    });
                                }
                            });
                            
                            $field->help("Showing cities in {$region} region");
                        } else {
                            $field->help('Select a region to filter cities');
                        }
                    }
                )
                ->ajaxSearchable(true)
                ->help('This field depends on both region and state born in'),

            SelectPlus::make('Favorite Coffee', 'favorite_coffee')
                ->required()
                ->options(function ($request = null) {
                    $coffees = Http::get('https://api.sampleapis.com/coffee/hot')
                        ->collect()
                        ->map(fn ($coffee) => ['value' => $coffee['id'], 'label' => $coffee['title']]);

                    if ($request->has('search')) {
                        $coffees = $coffees->filter(fn ($coffee) => str_contains($coffee['label'], $request->get('search')));
                    }

                    return $coffees->values();
                })
                ->fillUsing(function ($request, $model, $attribute) {
                    $model->favorite_coffee = collect(json_decode($request->get('favorite_coffee'), true))
                        ->pluck('label')
                        ->toArray();
                })
                // ->ajaxSearchable(true)
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
