# Nova Select Plus

## Installation

```
composer require ziffmedia/nova-select-plus
```

## Description & Use Cases

This Nova component was built to satisfy the use cases just beyond Nova's built-in `<select>` component. Here are
some scenarios where you might want `SelectPlus` (which uses `vue-select`) over the simple `Select`:

#### Select For BelongsToMany and MorphsToMany On the Form Screen

The default Nova experience for `BelongsToMany` and `MorphsToMany` is to have a separate UI screen for
attaching/detaching and syncing relationships through a "Pivot" model. For simple relationships (relationships that do
not have addition pivot values or the only value in the pivot table is there for ordering), it is benefitial to move
this Selection to the Form workflow instead of a separate workflow.

#### Ajax For Options

For Select's that have between a handful to several 1000 options, it is more peformant to load the full list of options
only on the screen that needs it: the Form screen.

There are 2 options for Ajax Options, the default is to load them all on the Form load. The second is to allow for full
option searching (in this case you can write you own ajax search resolver).

### Reordering Simple Pivot/BelongsToMany Relations

Through `->reorderable()`, you can enable a `SelectPlus` field to be reorderable. This allows, at `BelongsToMany->sync()`
time, to populate a pivot value useful for ordering relations.

## Usage

```php
SelectPlus::make('Authors')

SelectPlus::make('Favorite Books', 'favoriteBooks', Books::class) // including the relation method & Nova Resource for relation
```

### Options & Examples

#### `->label($attribute)` Pick a different attribute to use as the label

`Default: 'name'`

#### `->usingIndexLabel()` & `->usingDetailLabel()`

Default is to produce a count of the number of items on the index and detail screen

![alt text](https://github.com/ziffmedia/nova-select-plus/raw/master/docs/1-default.png "Default Index")

If a *string* name is provided, the name attribute is plucked and comma joined:

```php
SelectPlus::make('States Lived In', 'statesLivedIn', State::class)
  ->usingIndexLabel('name'),
```

![alt text](https://github.com/ziffmedia/nova-select-plus/raw/master/docs/2-usingIndexLable-string.png "string and comma separated")

If a closure is provided, it will be called, and the value will be utilized.  If the value is a string, it will be placed:

```php
SelectPlus::make('States Lived In', 'statesLivedIn', State::class)
  ->usingIndexLabel(fn($models) => $models->first()->name ?? ''),
```

![alt text](https://github.com/ziffmedia/nova-select-plus/raw/master/docs/3-usingIndexLable-callback.png "return just the first name")

If an array is returned, the Index and Detail screens will produce a `<ol>` or `<ul>` list:

```php
SelectPlus::make('States Lived In', 'statesLivedIn', State::class)
  ->usingIndexLabel(fn($models) => $models->pluck('name')),
```

![alt text](https://github.com/ziffmedia/nova-select-plus/raw/master/docs/4-usingDetailLabel-array.png "array of values")

#### `->reorderable($pivotOrderAttribute)` - Ability to reorder multiple selects

```php
    // assuming in the User model:
    public function statesVisited()
    {
        return $this->belongsToMany(State::class, 'state_user_visited')
            ->withPivot('order')
            ->orderBy('order')
            ->withTimestamps();
    }

    // inside the Nova resource:
    SelectPlus::make('States Lived In', 'statesLivedIn', State::class)
        ->reorderable('order'),
```

![alt text](https://github.com/ziffmedia/nova-select-plus/raw/master/docs/5-reorderable.gif "reorder a list")

#### `->ajaxSearchable(string|closure|true)` Ajax search for values

Given a string, models will be search the resources via the provided attribute using WHERE LIKE. Given a callback,
returning a Collection will populate the dropdown:

```php
    SelectPlus::make('States Visited', 'statesVisited', State::class)
        ->ajaxSearchable(function ($search) {
            return StateModel::where('name', 'LIKE', "%{$search}%")->limit(5);
        })
```

![alt text](https://github.com/ziffmedia/nova-select-plus/raw/master/docs/6-ajaxSearchable.gif "reorder a list")
