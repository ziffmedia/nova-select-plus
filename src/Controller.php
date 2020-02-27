<?php

namespace ZiffMedia\NovaSelectPlus;

use Laravel\Nova\Http\Requests\NovaRequest;

class Controller
{
    public function options(NovaRequest $request, $resource, $relationship)
    {
        /** @var SelectPlus $field */
        $field = $request->newResource()
            ->availableFields($request)
            ->where('component', 'relation-multiselect')
            ->where('attribute', $relationship)
            ->first();

        $query = $field->relationshipResource::newModel();

        return response()->json($field->mapToSelectionValue(
            $query->all()
        ));
    }
}
