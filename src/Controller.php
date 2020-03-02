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
            $search = $request->get('search');
            
            if (is_callable($field->ajaxSearchable)) {
                $return = call_user_func($field->ajaxSearchable, $search, $query);

                if ($return instanceof Builder) {
                    $query = $return;
                }                
            } elseif (is_string($field->ajaxSearchable)) {
                $query->where($field->ajaxSearchable, 'LIKE', "%{$search}%");
            } elseif ($field->ajaxSearchable === true) {
                $query->where($field->label, 'LIKE', "%{$search}%");
            }
        }

        return response()->json($field->mapToSelectionValue(
            (isset($return) && $return instanceof Collection)
                ? $return
                : $query->get()
        ));
    }
}
