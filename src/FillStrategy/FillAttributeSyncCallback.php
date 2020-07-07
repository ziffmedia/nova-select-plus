<?php

namespace ZiffMedia\NovaSelectPlus\FillStrategy;

use Illuminate\Support\Collection;

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

        $this->model->{$this->attribute}()->sync($syncValues);
    }
}

