<?php

namespace ZiffMedia\NovaSelectPlus;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Laravel\Nova\Http\Requests\NovaRequest;
use RuntimeException;

class Controller
{
    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function options(NovaRequest $request, $resource, $relationship)
    {
        /** @var SelectPlus $field */
        $field = $request->newResource()
            ->availableFields($request)
            ->where('component', 'select-plus')
            ->where('attribute', $relationship)
            ->first();

        /** @var Builder $model */
        $query = $field->relationshipResource::newModel()->newQuery();

        // pull out resourceId for use in querying callbacks
        $resourceId = $request->get('resourceId');

        if ($field->optionsQuery) {
            $this->application->call($field->optionsQuery, compact('query', 'request', 'resourceId'));
        }

        if ($field->ajaxSearchable) {
            $search = $request->get('search');

            if (is_callable($field->ajaxSearchable)) {

                $return = $this->application->call($field->ajaxSearchable, compact('query', 'request', 'search', 'resourceId'));

                if ($return instanceof Builder) {
                    $query = $return;
                }
            } elseif (is_string($field->ajaxSearchable)) {
                $query->where($field->ajaxSearchable, 'LIKE', "%{$search}%");
            } elseif ($field->ajaxSearchable === true) {
                if (is_string($field->label)) {
                    $query->where($field->label, 'LIKE', "%{$search}%");
                } else {
                    // this should never happen as this situation should be caught in the resolve() of the SelectNova field
                    throw new RuntimeException('Something went wrong ¯\_(ツ)_/¯');
                }
            }
        }

        return response()->json($field->mapToSelectionValue(
            (isset($return) && $return instanceof Collection)
                ? $return
                : $query->get()
        ));
    }
}
