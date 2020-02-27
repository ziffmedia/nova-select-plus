<?php

namespace ZiffMedia\NovaSelectPlus;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;

class Controller
{
    public function options(NovaRequest $request, $resource, $relationship)
    {
        /** @var SelectPlus $field */
        $field = $request->newResource()
            ->availableFields($request)
            ->where('component', 'select-plus')
            ->where('attribute', $relationship)
            ->first();

        /** @var Builder $model */
        $query = $field->relationshipResource::newModel()->newModelQuery();

        if ($field->ajaxSearchable !== null && $request->has('search')) {
            call_user_func($field->ajaxSearchable, $query, $request->get('search'));
        }

        return response()->json($field->mapToSelectionValue(
            $query->get()
        ));
    }
}
