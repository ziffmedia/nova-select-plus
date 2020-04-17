<?php

namespace ZiffMedia\NovaSelectPlus;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InvalidArgumentException;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use RuntimeException;

class SelectPlus extends Field
{
    public $component = 'select-plus';

    public $relationshipResource = null;

    public $label = 'name';

    public $indexLabel = null;
    public $detailLabel = null;

    public $valueForIndexDisplay = null;
    public $valueForDetailDisplay = null;

    public $maxSelections = null;
    public $ajaxSearchable = null;
    public $reorderable = null;

    public function __construct($name, $attribute = null, $relationshipResource = null, $label = 'name')
    {
        parent::__construct($name, $attribute);

        $this->relationshipResource = $relationshipResource ?? ResourceRelationshipGuesser::guessResource($name);

        if (!class_exists($this->relationshipResource)) {
            throw new RuntimeException("Relationship Resource {$this->relationshipResource} is not a valid class");
        }

        $this->label($label);
    }

    public function label($label)
    {
        if (!(is_string($label) || is_callable($label))) {
            throw new InvalidArgumentException('label() must be a string or callable');
        }

        $this->label = $label;

        return $this;
    }

    /**
     * @param string|callable $indexLabel
     * @return $this
     */
    public function usingIndexLabel($indexLabel)
    {
        $this->indexLabel = $indexLabel;

        return $this;
    }

    /**
     * @param string|callable $detailLabel
     * @return $this
     */
    public function usingDetailLabel($detailLabel)
    {
        $this->detailLabel = $detailLabel;

        return $this;
    }

    public function maxSelections($maxSelections)
    {
        $this->maxSelections = $maxSelections;

        return $this;
    }

    public function ajaxSearchable($ajaxSearchable)
    {
        $this->ajaxSearchable = $ajaxSearchable;

        return $this;
    }

    public function reorderable(string $orderAttribute)
    {
        $this->reorderable = $orderAttribute;

        return $this;
    }

    /**
     * @param mixed|Resource|Model $resource
     * @param null $attribute
     */
    public function resolve($resource, $attribute = null)
    {
        // use base functionality to populate $this->value
        parent::resolve($resource, $attribute);

        if ($this->ajaxSearchable && !is_callable($this->ajaxSearchable) && is_callable($this->label)) {
            throw new RuntimeException('"' . $this->name . '" as a ' . __CLASS__
                . ' has a dynamic (function) for label(), when using ajaxSearchable() and label(fn), ajaxSearchable() must also be dynamic (function).'
            );
        }

        // handle setting up values for relations
        if (method_exists($resource, $this->attribute)) {
            $this->resolveForRelations($resource);

            return;
        }

        throw new RuntimeException('Currently attributes are not yet supported');

        // @todo $this->resolveForAttribute($resource);
    }

    protected function resolveForRelations($resource)
    {
        $relationQuery = $resource->{$this->attribute}();

        if (!$relationQuery instanceof BelongsToMany) {
            throw new RuntimeException('This field currently only supports MorphsToMany and BelongsToMany');
        }

        // if the value is requested on the INDEX field, we need to roll it up to show something
        if ($this->indexLabel) {
            $this->valueForIndexDisplay = is_callable($this->indexLabel)
                ? call_user_func($this->indexLabel, $this->value)
                : $this->value->pluck($this->indexLabel)->implode(', ');
        } else {
            $count = $this->value->count();

            $this->valueForIndexDisplay = $count . ' ' . $this->name; // example: "5 states"
        }

        if ($this->detailLabel) {
            $this->valueForDetailDisplay = is_callable($this->detailLabel)
                ? call_user_func($this->detailLabel, $this->value)
                : $this->value->pluck($this->detailLabel)->implode(', ');
        } else {
            $count = $this->value->count();

            $this->valueForDetailDisplay = $count . ' ' . $this->name;
        }

        // convert to {key: xxx, label: xxx} format
        $this->value = $this->mapToSelectionValue($this->value);
    }

    protected function resolveForAttribute($resource)
    {
        if ($this->options === null) {
            throw new RuntimeException('For attributes using SelectPlus, options() must be available');
        }

        $casts = $resource->getCasts();

        // @todo do things specific to the kind of cast it is, or throw exception, if no cast, assume its options with string types
    }

    protected function fillAttribute(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        // returning a function allows this to run after the model has been saved (which is crucial if this is a new model)
        return function () use ($request, $requestAttribute, $model, $attribute) {
            $values = collect(json_decode($request[$requestAttribute], true));

            $keyName = $model->getKeyName();

            if ($this->reorderable) {
                $syncValues = $values->mapWithKeys(function ($value, $index) use ($keyName) {
                    return [$value[$keyName] => [$this->reorderable => $index + 1]];
                });
            } else {
                $syncValues = $values->pluck($keyName);
            }

            $model->{$attribute}()->sync($syncValues);
        };
    }

    public function mapToSelectionValue(Collection $models)
    {
        return $models->map(function (Model $model) {
            // todo add order field
            return [
                $model->getKeyName() => $model->getKey(),
                'label' => $this->labelize($model)
            ];
        });
    }

    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'ajax_searchable'          => $this->ajaxSearchable !== null,
            'relationship_name'        => $this->attribute,
            'value_for_index_display'  => $this->valueForIndexDisplay,
            'value_for_detail_display' => $this->valueForDetailDisplay,
            'max_selections'           => $this->maxSelections,
            'reorderable'              => $this->reorderable !== null
        ]);
    }

    protected function labelize(Model $model)
    {
        if (is_callable($this->label)) {
            return ($this->label)($model);
        }

        return $model->{$this->label};
    }
}

