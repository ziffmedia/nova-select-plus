<?php

namespace ZiffMedia\NovaSelectPlus;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
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
            $return = call_user_func($field->ajaxSearchable, $request->get('search'), $query);
        }

        return response()->json($field->mapToSelectionValue(
            (isset($return) && $return instanceof Collection)
                ? $return
                : $query->get()
        ));
    }
}
