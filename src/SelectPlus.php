<?php

namespace ZiffMedia\NovaSelectPlus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use RuntimeException;

class SelectPlus extends Field
{
    use SupportsDependentFields;

    public static array $selectPlusFields = [];

    public ?string $fieldId = null;

    public $component = 'select-plus';

    public $label = 'name';

    public $indexLabel = null;

    public $detailLabel = null;

    public $valueForIndexDisplay = null;

    public $valueForDetailDisplay = null;

    public $options = null;

    public $optionsQuery = null;

    public int|null $maxSelections = null;

    public $ajaxSearchable = null;

    public bool $ajaxSearchableEmptySearch = false;

    public $mapValuesToSelectionOptions = null;

    public $reorderable = null;

    public function __construct($name, $relationMethodOrAttribute = null, $relationshipResource = null, $label = null)
    {
        $this->name = $name;

        parent::__construct($name, $relationMethodOrAttribute);

        if ($relationshipResource) {
            trigger_error('DEPRECATED: $relationshipResource is deprecated and should not be passed in, use options() instead');

            $this->options($relationshipResource);
        }

        if ($label) {
            trigger_error('DEPRECATED: $label is deprecated in SelectPlus::make() and should not be passed in, use label() instead');

            $this->label($label);
        }
    }

    public function withFieldId(string $fieldId): static
    {
        $this->fieldId = $fieldId;

        static::$selectPlusFields[$fieldId] = $this;

        return $this;
    }

    /**
     * @param class-string<Model>|Collection|array|callable $options
     * @return $this
     */
    public function options($options): static
    {
        $this->options = $options;

        return $this;
    }

    public function withScout()
    {
        // @todo
    }

    public function label($label): static
    {
        if (! (is_string($label) || is_callable($label))) {
            throw new InvalidArgumentException('label() must be a string or callable');
        }

        $this->label = $label;

        return $this;
    }

    public function usingIndexLabel(callable|string $indexLabel): static
    {
        $this->indexLabel = $indexLabel;

        return $this;
    }

    public function usingDetailLabel(callable|string $detailLabel): static
    {
        $this->detailLabel = $detailLabel;

        return $this;
    }

    public function optionsQuery(callable $optionsQuery): static
    {
        $this->optionsQuery = $optionsQuery;

        return $this;
    }

    public function maxSelections($maxSelections): static
    {
        $this->maxSelections = $maxSelections;

        return $this;
    }

    public function ajaxSearchable($ajaxSearchable, $ajaxSearchableEmptySearch = false): static
    {
        $this->ajaxSearchable = $ajaxSearchable;
        $this->ajaxSearchableEmptySearch = $ajaxSearchableEmptySearch;

        return $this;
    }

    public function reorderable(string $orderAttribute): static
    {
        $this->reorderable = $orderAttribute;

        return $this;
    }

    /**
     * @param  mixed|resource|Model  $resource
     * @param  null  $attribute
     */
    public function resolve($resource, $attribute = null): void
    {
        $attribute = $this->attribute;

        // state checking: can't use options() and relations at same time
        if ($this->options && $resource->isRelation($attribute)) {
            throw new RuntimeException("{$attribute} field cannot use options() with a relation");
        }

        if ($this->resolveCallback) {
            tap($this->resolveAttribute($resource, $this->attribute), function ($value) use ($resource, $attribute) {
                $this->value = call_user_func($this->resolveCallback, $value, $resource, $attribute);
            });

            return;
        }

        // use base functionality to populate $this->value, load in case Lazy Loading is disabled
        if (method_exists($resource, $attribute)) {
            $resource->loadMissing($attribute);
        }

        parent::resolve($resource, $attribute);

        if ($this->ajaxSearchable && ! is_callable($this->ajaxSearchable) && is_callable($this->label)) {
            throw new RuntimeException('"'.$this->name.'" as a '.__CLASS__
                .' has a dynamic (function) for label(), when using ajaxSearchable() and label(fn), ajaxSearchable() must also be dynamic (function).'
            );
        }

        // RELATIONS
        if ($resource->isRelation($attribute)) {
            $relation = $resource->{$attribute}();

            if ($relation instanceof BelongsTo || $relation instanceof MorphTo) {
                if ($this->maxSelections != 1) {
                    if ($this->maxSelections === null) {
                        $this->maxSelections = 1;
                    } else {
                        throw new RuntimeException($attribute . 'is a BelongsTo/MorphTo, but maxSelections was not set to 1');
                    }
                }

                $belongsToValue = $this->value;
                $value = Collection::wrap($this->value);
            } else {
                $value = $this->value;
            }

            $this->value = $this->mapValuesToSelectionOptions($value);
        }

        // ATTRIBUTES

        if (! $resource->isRelation($attribute)) {
            $castType = $resource->getCasts()[$attribute] ?? null;

            if (is_array($this->options)
                && in_array($castType, ['integer', 'string'])
                && !Arr::isAssoc($this->options)
                && $this->maxSelections != 1
                && !$this->fillCallback
            ) {
                throw new RuntimeException('To store SelectPlus values from an options() array to an attribute of integer or string, you must also set maxSelections to 1, or use your own fillCallback');
            }

            $this->value = $this->mapValuesToSelectionOptions(
                Collection::wrap($this->value)
            );
        }

        // if the value is requested on the INDEX field, we need to roll it up to show something
        if ($this->indexLabel) {
            $this->valueForIndexDisplay = is_callable($this->indexLabel)
                ? call_user_func($this->indexLabel, $this->value)
                : $this->value->pluck($this->indexLabel)->implode(', ');
        } else {
            if (isset($relation) && ($relation instanceof BelongsTo || $relation instanceof MorphTo)) {
                $this->valueForIndexDisplay = isset($belongsToValue)
                    ? $belongsToValue->{$this->indexLabel} ?? $belongsToValue->{$this->label}
                    : 'None';
            } else {
                $this->valueForIndexDisplay = $this->value->count().' '.$this->name; // example: "5 states"
            }
        }

        if ($this->detailLabel) {
            $this->valueForDetailDisplay = is_callable($this->detailLabel)
                ? call_user_func($this->detailLabel, $this->value)
                : $this->value->pluck($this->detailLabel)->implode(', ');
        } else {
            if (isset($relation) && ($relation instanceof BelongsTo || $relation instanceof MorphTo)) {
                $this->valueForDetailDisplay = isset($belongsToValue)
                    ? $belongsToValue->{$this->detailLabel} ?? $belongsToValue->{$this->label}
                    : 'None';
            } else {
                $this->valueForDetailDisplay = $this->value->count().' '.$this->name;
            }
        }
    }

    public function fill(NovaRequest $request, $model)
    {
        /** @var Model $model */
        /** @var string $attribute */
        $attribute = $this->attribute;

        $values = collect(json_decode($request[$this->attribute], true));

        if (isset($this->fillCallback)) {
            return call_user_func($this->fillCallback, $request, $model, $attribute, $attribute);
        }

        // RELATIONS

        if ($model->isRelation($attribute)) {
            $relation = $model->{$this->attribute}();
            $relatedModel = $relation->getRelated();
            $relatedModelKeyName = $relatedModel->getKeyName();

            if ($relation instanceof BelongsToMany || $relation instanceof MorphToMany) {
                // returning an invokable allows this to run after the model has been saved (which is crucial if this is a new model)
                return function () use ($relatedModelKeyName, $values, $relation) {
                    if ($this->reorderable) {
                        $syncValues = $values->mapWithKeys(function ($value, $index) use ($relatedModelKeyName) {
                            return [$value[$relatedModelKeyName] => [$this->reorderable => $index + 1]];
                        });
                    } else {
                        $syncValues = $values->pluck($relatedModelKeyName);
                    }

                    $relation->sync($syncValues);
                };
            } elseif ($relation instanceof BelongsTo || $relation instanceof MorphTo) {
                if ($values->count() === 1) {
                    $firstValue = $values->first();
                    $relation->associate($firstValue[$relatedModelKeyName]);
                } else {
                    $relation->disassociate();
                }

                return null;
            } else {
                throw new RuntimeException('Currently does not support relations of type' . get_class($relation));
            }
        }

        // ATTRIBUTES
        $castType = $model->getCasts()[$this->attribute] ?? null;

        // first case: nothing selected
        if ($values->count() === 0) {
            $model->{$this->attribute} = null;

            return null;
        }

        // if options was a Model, we can simply store just the id's
        if (is_string($this->options) && class_exists($this->options)) {
            /** @var Model $relatedModel */
            $relatedModel = new $this->options;

            if (in_array($castType, ['json', 'array'])) {
                $model->{$this->attribute} = $values->pluck($relatedModel->getKeyName())->toArray();
            } else {
                $model->{$this->attribute} = $values->pluck('label')->implode(', ');
            }

            return null;
        }

        // special case: simple options() selecting 1 value to put into a scalar cast column
        if (is_array($this->options)
            && in_array($castType, ['integer', 'string'])
            && !Arr::isAssoc($this->options)
            && $values->count() > 0
        ) {
            $model->{$this->attribute} = $values->first()['value'];

            return null;
        }

        // if the options was a provided simple array, we can store just the values
        if (is_array($this->options)) {
            $model->{$this->attribute} = $values->pluck('value')->toArray();

            return null;
        }

        $model->{$attribute} = $values->toArray();
    }

    public function mapSelectionOptionsToValues($selectionOptions, string $pluck = null)
    {

    }

    public function withMapValuesToSelectionOptions(callable $mapValuesToSelectionOptions): static
    {
        $this->mapValuesToSelectionOptions = $mapValuesToSelectionOptions;

        return $this;
    }

    public function mapValuesToSelectionOptions(Collection $options): Collection
    {
        if ($this->mapValuesToSelectionOptions) {
            $options = ($this->mapValuesToSelectionOptions)($options);

            if (!$options instanceof Collection) {
                throw new RuntimeException('withMapToSelectionValues(closure) must return a collection');
            }

            if ($options->count() > 0) {
                $first = $options->first;

                if (!isset($first['label'])) {
                    throw new RuntimeException('Mapped Selections in SelectPlus must have a key named "label"');
                }
            }

            return $options;
        }

        return $options->map(function ($option) {
            if ($option instanceof Model) {
                return [
                    $option->getKeyName() => $option->getKey(),
                    'label' => $this->labelize($option)
                ];
            }

            if (is_array($option) && Arr::has($option, ['value', 'label'])) {
                return $option;
            }

            if (is_scalar($option)) {
                return [
                    'value' => $option,
                    'label' => $this->labelize($option)
                ];
            }

            throw new RuntimeException('Currently $options is expected to be a Collection of Models, or [:value, :label] tuples when not using withMapToSelectionValues');
        });
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'fieldId' => $this->fieldId,
            'isAjaxSearchable' => $this->ajaxSearchable !== null,
            'isAjaxSearchableEmptySearch' => (bool) $this->ajaxSearchableEmptySearch,
            'relationshipName' => $this->attribute,
            'valueForIndexDisplay' => $this->valueForIndexDisplay,
            'valueForDetailDisplay' => $this->valueForDetailDisplay,
            'maxSelections' => $this->maxSelections,
            'isReorderable' => $this->reorderable !== null,
        ]);
    }

    protected function labelize($label)
    {
        if (is_callable($this->label)) {
            return ($this->label)($label);
        }

        if ($label instanceof Model && is_string($this->label)) {
            return $label->{$this->label};
        }

        return $label;
    }
}
