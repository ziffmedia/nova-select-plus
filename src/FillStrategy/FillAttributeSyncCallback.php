<?php

namespace ZiffMedia\NovaSelectPlus\FillStrategy;

use Illuminate\Support\Collection;
use RuntimeException;

class FillAttributeSyncCallback
{
    protected $relationshipResource;
    protected $fillAttributeValues;
    protected $model;
    protected $attribute;
    protected $reorderableColumn;

    public function __construct($relationshipResource, Collection $fillAttributeValues, $model, $attribute, $reorderableColumn)
    {
        $this->relationshipResource = $relationshipResource;
        $this->fillAttributeValues = $fillAttributeValues;
        $this->model = $model;
        $this->attribute = $attribute;
        $this->reorderableColumn = $reorderableColumn;
    }

    public function __invoke()
    {
        $keyName = ($this->relationshipResource)::newModel()->getKeyName();

        if ($this->reorderableColumn) {
            $syncValues = $this->fillAttributeValues->mapWithKeys(function ($value, $index) use ($keyName) {
                return [$value[$keyName] => [$this->reorderableColumn => $index + 1]];
            });
        } else {
            $syncValues = $this->fillAttributeValues->pluck($keyName);
        }

        if (!is_callable([$this->model, $this->attribute])) {
            throw new RuntimeException(
                "{$this->model}::{$this->attribute} must be a relation method to use " . __CLASS__
                . '; maybe you want to change your model to have a relation or implement your own fillUsing()?'
            );
        }

        $relation = $this->model->{$this->attribute}();

        if (!method_exists($relation, 'sync')) {
            throw new RuntimeException(
                "{$this->model}::{$this->attribute} does not appear to model a BelongsToMany or MorphsToMany"
            );
        }

        $relation->sync($syncValues);
    }
}

