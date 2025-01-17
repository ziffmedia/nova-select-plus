<?php

namespace ZiffMedia\NovaSelectPlus;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use RuntimeException;

class Controller
{
    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function options(NovaRequest $request, $resourceName, $relationship)
    {
        $resource = $request->newResource();

        $field = null;

        /** @var SelectPlus $field */
        $fields = $resource->availableFields($request)->filter(
            fn ($f) => $f instanceof Field || $f instanceof Panel
        );

        if ($request->has('fieldId')) {
            $field = SelectPlus::$selectPlusFields[$request->get('fieldId')];
        }

        if (! $field) {
            $field = $fields->applyDependsOnWithDefaultValues($request)
                ->where('component', 'select-plus')
                ->where('attribute', $relationship)
                ->first();
        }

        if (! $field) {
            throw new RuntimeException('A field was not identified, if this field is nested, use withFieldId($uniqueName) so options can be loaded.');
        }

        $model = $resource->model();

        if ($model->isRelation($field->attribute)) {
            $result = $this->processQuery(
                $model->{$field->attribute}()->getRelated()->newQuery(),
                $request,
                $field
            )->get();

            return response()->json($field->mapValuesToSelectionOptions($result));
        }

        if ($field->options && is_callable($field->options)) {
            $result = $this->application->call($field->options, compact('request'));
        } elseif ($field->options && (is_array($field->options) || ($field->options instanceof Enumerable))) {
            $result = Collection::wrap($field->options);
        } elseif (class_exists($field->options) && is_subclass_of($field->options, Model::class)) {
            $result = $this->processQuery(
                (new $field->options)->newQuery(),
                $request,
                $field
            )->get();
        } elseif ($field->options) {
            throw new RuntimeException('options() must be an array, callable, class');
        } else {
            throw new RuntimeException("When {$field->attribute} is not a relation, you must also provide options()");
        }

        return response()->json($field->mapValuesToSelectionOptions($result));
    }

    protected function processQuery(Builder $query, NovaRequest $request, SelectPlus $field): Builder
    {
        // pull out resourceId for use in querying callbacks
        $resourceId = $request->get('resourceId');

        if ($field->optionsQuery) {
            $this->application->call($field->optionsQuery, compact('query', 'request', 'resourceId'));
        }

        if ($field->ajaxSearchable) {
            $search = $request->get('search');

            if (is_callable($field->ajaxSearchable)) {
                $result = $this->application->call($field->ajaxSearchable, compact('query', 'request', 'search', 'resourceId'));

                if ($result instanceof Builder) {
                    $query = $result;
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

        return $query;
    }
}
