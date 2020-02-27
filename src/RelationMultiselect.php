<?php

namespace RalphSchindler\NovaRelationMultiselect;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class RelationMultiselect extends Field
{
    public $component = 'relation-multiselect';

    public $relationshipResource = null;

    public $indexLabel = null;
    public $detailLabel = null;
    public $formLabel = 'name';

    public $options = null;

    public $valueForIndexDisplay = null;
    public $valueForDetailDisplay = null;

    public $maxSelections = null;

    public function __construct($name, $attribute = null, $relationshipResource = null)
    {
        parent::__construct($name, $attribute);

        $this->relationshipResource = $relationshipResource ?? ResourceRelationshipGuesser::guessResource($name);
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

    /**
     * @param string|callable $formLabel
     * @return $this
     */
    public function usingFormLabel($formLabel)
    {
        $this->formLabel = $formLabel;

        return $this;
    }

    public function maxSelections($maxSelections)
    {
        $this->maxSelections = $maxSelections;

        return $this;
    }

    public function options($options)
    {
        $this->options = $options;

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

        // handle setting up values for relations
        if (method_exists($resource, $this->attribute)) {
            $this->resolveForRelations($resource);

            return;
        }

        $this->resolveForAttribute($resource);
    }

    protected function resolveForRelations($resource)
    {
        $relationQuery = $resource->{$this->attribute}();

        if (!$relationQuery instanceof BelongsToMany) {
            throw new \RuntimeException('This field currently only supports MorphsToMany and BelongsToMany');
        }

        // if the value is requested on the INDEX field, we need to roll it up to show something
        if ($this->indexLabel) {
            $this->valueForIndexDisplay = is_callable($this->indexLabel)
                ? call_user_func($this->indexLabel, $this, $resource)
                : $this->value->pluck($this->indexLabel)->implode(', ');
        } else {
            $count = $this->value->count();

            $this->valueForIndexDisplay = $count . ' ' . Str::plural(Str::singular($this->name), $count);
        }

        // if the value is requested on the DETAIL field, we need to roll it up to show something
        if ($this->detailLabel) {
            $this->valueForDetailDisplay = is_callable($this->detailLabel)
                ? call_user_func($this->detailLabel, $this, $resource)
                : $this->value->pluck($this->detailLabel)->implode(', ');
        } else {
            $count = $this->value->count();

            $this->valueForDetailDisplay = $count . ' ' . Str::plural(Str::singular($this->name), $count);
        }

        // convert to {key: xxx, label: xxx} format
        $this->value = $this->mapToSelectionValue($this->value);
    }

    protected function resolveForAttribute($resource)
    {
        if ($this->options === null) {
            throw new \RuntimeException('For attributes using RelationalMultiselect, options() must be available');
        }

        $casts = $resource->getCasts();

        // @todo do things specific to the kind of cast it is, or throw exception, if no cast, assume its options with string types
    }

    protected function fillAttribute(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        // returning a function allows this to run after the model has been saved (which is crucial if this is a new model)
        return function () use ($request, $requestAttribute, $model, $attribute) {
            $values = collect(json_decode($request[$requestAttribute], true))->pluck('key');

            $model->{$attribute}()->sync($values);
        };
    }

    public function mapToSelectionValue(Collection $models)
    {
        return $models->map(function (Model $model) {
            return ['key' => $model->getKey(), 'label' => $model->{$this->formLabel}]; // todo add order field
        });
    }

    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'relationship_name'        => $this->attribute,
            'value_for_index_display'  => $this->valueForIndexDisplay,
            'value_for_detail_display' => $this->valueForDetailDisplay,
            'max_selections'           => $this->maxSelections
        ]);
    }
}

